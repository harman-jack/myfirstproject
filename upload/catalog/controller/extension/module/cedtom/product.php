<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category  modules
 * @package   CedTom
 * @author    CedCommerce Core Team
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */
class ControllerExtensionModuleCedtomProduct extends Controller
{
    public function syncImages()
    {
    	$data = 'Something went wrong !';

    	$query = $this->db->query("SELECT * FROM `".DB_PREFIX."cedtom_product_image` ORDER BY id ASC LIMIT 10");

    	if($query->num_rows) {
    		$temp = array();
    		$ids = array();
    		$cedtom = new Cedtom($this->registry);

    		foreach($query->rows as $row)  { 
    			$ids[] = $row['id'];
    			$image_url = $row['image'];
    			$tom_product_id = str_replace('', '_', $row['tom_product_id']);

    			$exploded_data = explode('.', $image_url);
    			$image_extension = end($exploded_data);

    			$save_to = $this->prepareurl($tom_product_id, $image_extension, $row['product_id'], $row['sort_order']);
				$cedtom->grab_image($image_url, DIR_IMAGE.$save_to);

				if($row['sort_order']) {
					$this->db->query("INSERT INTO `".DB_PREFIX."product_image` SET product_id = '".(int)$row['product_id']."', image = '".$this->db->escape($save_to)."', sort_order = '".(int)$row['sort_order']."'");
				}else{
					$this->db->query("UPDATE `".DB_PREFIX."product` SET image = '".$this->db->escape($save_to)."' WHERE product_id = '".(int)$row['product_id']."'");
				}

				$temp[] = $row['product_id'] .'_image_'. $row['sort_order'];
			}

			$this->db->query("DELETE FROM `".DB_PREFIX."cedtom_product_image` WHERE `id` IN (".implode(',',$ids).")");

			$data = implode('<br/>', $temp). '<br/>Images dowloaded successfully.';

    	}else{
    		$data = 'No image found for Syncing';
    	}

    	print_r($data);die;
    }

    protected function prepareurl($tom_product_id, $image_extension, $product_id, $sort_order) {

    	$local_image_path = 'catalog/products/'. $tom_product_id .'_'.$product_id.'_'.$sort_order.'.'.$image_extension ;

        if(!is_dir(DIR_IMAGE . 'catalog/products')){
    		@mkdir(DIR_IMAGE . 'catalog/products' , 0777, true);
    	}

    	$fp = fopen(DIR_IMAGE.$local_image_path,"wb");
		fclose($fp);
        
        return $local_image_path;
    }

    public function syncInventory() {
        $data = 'Something went wrong !';

        $query = $this->db->query("SELECT * FROM `".DB_PREFIX."cedtom_product` fp LEFT JOIN `".DB_PREFIX."product` p ON(fp.product_id=p.product_id) ");

        if($query->num_rows) {
            $cedtom = new Cedtom($this->registry);
            $data = 'No Updates In Inventory';
            $temp = array();
                
            $url = 'api/twm/products/changes/10';

            $method = "GET";
        
            $response = $cedtom->curlRequest($url, [], $method);

            foreach($query->rows as $row) { 
                foreach($response as $item) {

                    if($item['id'] == $row['tom_product_id']) {
                        $temp[] = $row['product_id']; 
            
                        $this->db->query("UPDATE `".DB_PREFIX."product` SET quantity = '".$item['stock']."' WHERE product_id = '".(int)$row['product_id']."'");
                    }
                }
            }
            if(!empty($temp)){
                $data = implode('<br/>', $temp). '<br/>Product Inventory sync successfully.';
            }
        }else{
            $this->db->query("UPDATE `".DB_PREFIX."cedtom_product` SET quantity_update = 0");

            $data = "No data found for Inventory syncing.";
        }

        print_r($data);die;
    }
}