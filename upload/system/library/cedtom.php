<?php

class Cedtom
{
    private $access_token;
    private $registry;
    private $status;
    private $mode;
    private $language_id;
    private $store_id;
    private $currency_id;

    private static $instance;

    public function __construct($registry)
    {
        $this->db = $registry->get('db');
        $this->session = $registry->get('session');
        $this->config = $registry->get('config');
        $this->currency = $registry->get('currency');
        $this->request = $registry->get('request');
        $this->weight = $registry->get('weight');
        $this->openbay = $registry->get('openbay');
        
        $this->registry = $registry;
        $this->api_url = $this->config->get('cedtom_api_url');
        $this->api_token = $this->config->get('cedtom_api_token');

        $this->language_id = $this->config->get('cedtom_store_language');
    }

    public function log($data, $force_log = false, $step = 6)
    {

        if ($force_log) {
            $log = new Log('cedtom.log');
            if (is_array($data))
                $data = json_encode($data);
            
            $log->write($data);
        }
    }

    public static function getInstance($registry)
    {
        if (is_null(static::$instance)) {
            static::$instance = new static($registry);
        }

        return static::$instance;
    }

    public function isNewSetup()
    { 
        // segment  table creation
        if ($this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cedtom_segment'")->num_rows == 0) {
            $cedtom_order = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cedtom_segment` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `order_id` int(11) NOT NULL,
                  `tom_order_id` text CHARACTER SET utf8 NOT NULL,
                  `status` varchar(30) NOT NULL,
                  `sku` varchar(150) NOT NULL,
                  PRIMARY KEY (`id`)
              );";

            $status = $this->db->query($cedtom_order);

            $this->log('isNewSetup() - Ced Tom ' . DB_PREFIX . 'cedtom_order Creation status ' . $status);
        }

        // cedcedtom_product table creation
        if ($this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cedtom_product'")->num_rows == 0) {
            $cedtom_product = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cedtom_product` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `tom_product_id` varchar(50) NOT NULL,
                  `ean` varchar(50) NOT NULL,
                  `product_id` int(11) unsigned NOT NULL,
                  `status` text CHARACTER SET utf8 NOT NULL,
                  `product_data` longtext CHARACTER SET utf8 NOT NULL,
                  `quantity_update` int(1) unsigned NOT NULL,
                  PRIMARY KEY (`id`),
                  INDEX idx_id (`product_id`)
              );";

            $status = $this->db->query($cedtom_product);

            $this->log('isNewSetup() - Ced Tom ' . DB_PREFIX . 'cedtom_product Creation status ' . $status);
        }

        // cedtom_product_image table creation
        if ($this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cedtom_product_image'")->num_rows == 0) {
            $cedtom_product_image = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cedtom_product_image` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `tom_product_id` varchar(50) NOT NULL,
                  `product_id` int(11) unsigned NOT NULL,
                  `type` text CHARACTER SET utf8 NOT NULL,
                  `image` varchar(255) CHARACTER SET utf8 NOT NULL,
                  `sort_order` int(2) NOT NULL,
                  PRIMARY KEY (`id`),
                  INDEX idx_id (`product_id`)
              );";

            $status = $this->db->query($cedtom_product_image);

            $this->log('isNewSetup() - Ced Tom ' . DB_PREFIX . 'cedtom_product_image Creation status ' . $status);
        }

        // order  table creation
        if ($this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "cedtom_order'")->num_rows == 0) {
            $cedtom_order = "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "cedtom_order` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `order_id` int(11) NOT NULL,
                  `tom_order_id` text CHARACTER SET utf8 NOT NULL,
                  `status` varchar(30) NOT NULL,
                  `sku` varchar(150) NOT NULL,
                  PRIMARY KEY (`id`),
                  INDEX idx_id (`order_id`)
              );";

            $status = $this->db->query($cedtom_order);

            $this->log('isNewSetup() - Ced Tom ' . DB_PREFIX . 'cedtom_order Creation status ' . $status);
        }
    }

  public function curlRequest($url, $params = array(), $method = 'GET') {
    
    $this->isTokenValid();

    $token = $this->config->get('cedtom_token');

    $api_url = $this->config->get('cedtom_api_url').$url;

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $api_url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    if(!empty($params)){
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    }
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Content-type: application/json',
        "Authorization: Bearer ".$token,
    ));

    $response = curl_exec($curl);

    curl_close($curl);
   
    return json_decode($response, true);
  }

    protected function isTokenValid(){
        $interval = (array) date_diff(new DateTime(date('Y/m/d H:i:s')),new DateTime(date('Y-m-d H:i:s',$this->config->get('cedtom_time'))));

        if($interval['invert']){

            $url = 'api/twm/auth/authenticate';

            $params = array(
                'email' =>  $this->config->get('cedtom_email'),
                'password' =>  html_entity_decode($this->config->get('cedtom_password')),
            );

            $token = json_decode($this->getToken($url, $params), true);

            $this->log(json_encode($token));

            if(isset($token['token'])){
                $this->add('cedtom_token', $token['token']);
                $this->add('cedtom_time', $token['valid_until']);

                $this->config->set('cedtom_token', $token['token']);
                $this->config->set('cedtom_time', $token['valid_until']);
            }
        }
    }


    public function getToken($url, $params){
        $api_url = $this->config->get('cedtom_api_url').$url;

        $body = json_encode($params);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $api_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "cache-control: no-cache",
            "Content-type: application/json",
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        return $response;
    }

    public function add($key, $value){
        $result = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE `code` = 'cedtom' AND `key` = '" . $this->db->escape($key) . "' ");
        if(!$result->num_rows){
            $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '0', `code` = 'cedtom', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "', serialized = '0'");
        }else{
            $this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($value) . "' WHERE `code` = 'cedtom' AND `key` = '" . $this->db->escape($key) . "'");
        }
    }

    function grab_image($url,$saveto){

        $ch = curl_init ($url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
        $raw=curl_exec($ch);
        curl_close ($ch);

        if(file_exists($saveto)){
            unlink($saveto);
        }
        $fp = fopen($saveto,'x');
        fwrite($fp, $raw);
        fclose($fp);
    }

}

