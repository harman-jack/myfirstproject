<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-order" formaction="<?php echo $delete; ?>" data-toggle="tooltip" title="<?php echo $button_delete; ?>" class="btn btn-danger"><?php echo $button_delete; ?></button>
      </div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <?php if ($success) { ?>
    <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_list; ?></h3>
      </div>
      <div class="panel-body">
        <div class="well">
          <div class="row">
            <div class="col-sm-4">
              <div class="form-group">
                <label class="control-label" for="input-order-id"><?php echo $entry_order_id; ?></label>
                <input type="text" name="filter_order_id" value="<?php echo $filter_order_id; ?>" placeholder="<?php echo $entry_order_id; ?>" id="input-order-id" class="form-control submit_on_enter" />
              </div>
              <div class="form-group">
                <label class="control-label" for="input-order-status"><?php echo $entry_order_status; ?></label>
                <select name="filter_order_status" id="input-order-status" class="form-control" onchange="filter();">
                  <option value="*"></option>
                  <?php foreach ($order_statuses as $order_status) { ?>
                  <?php if ($order_status == $filter_order_status) { ?>
                  <option value="<?php echo $order_status; ?>" selected="selected"><?php echo $order_status; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $order_status; ?>"><?php echo $order_status; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select>
              </div> 
              <div class="form-group">
                <label class="control-label" for="input-date-modified"><?php echo $entry_date_modified; ?></label>
                <div class="input-group date">
                  <input type="text" name="filter_date_modified" value="<?php echo $filter_date_modified; ?>" placeholder="<?php echo $entry_date_modified; ?>" data-date-format="YYYY-MM-DD" id="input-date-modified" class="form-control submit_on_enter" />
                  <span class="input-group-btn">
                  <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                  </span></div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label class="control-label" for="input-flora-order-id"><?php echo $entry_flora_order_id; ?></label>
                <input type="text" name="filter_flora_order_id" value="<?php echo $filter_flora_order_id; ?>" placeholder="<?php echo $entry_flora_order_id; ?>" id="input-flora-order-id" class="form-control submit_on_enter" />
              </div> 
              <div class="form-group">
                <label class="control-label" for="input-total"><?php echo $entry_total; ?></label>
                <input type="text" name="filter_total" value="<?php echo $filter_total; ?>" placeholder="<?php echo $entry_total; ?>" id="input-total" class="form-control submit_on_enter" />
              </div>
            </div>
            <div class="col-sm-4">
              <div class="form-group">
                <label class="control-label" for="input-customer"><?php echo $entry_customer; ?></label>
                <input type="text" name="filter_customer" value="<?php echo $filter_customer; ?>" placeholder="<?php echo $entry_customer; ?>" id="input-customer" class="form-control submit_on_enter" />
              </div>
              <div class="form-group">
                <label class="control-label" for="input-date-added"><?php echo $entry_date_added; ?></label>
                <div class="input-group date">
                  <input type="text" name="filter_date_added" value="<?php echo $filter_date_added; ?>" placeholder="<?php echo $entry_date_added; ?>" data-date-format="YYYY-MM-DD" id="input-date-added" class="form-control submit_on_enter" />
                  <span class="input-group-btn">
                  <button type="button" class="btn btn-default"><i class="fa fa-calendar"></i></button>
                  </span></div>
              </div>
              <button type="button" id="button-filter" class="btn btn-primary pull-right"><i class="fa fa-filter"></i> <?php echo $button_filter; ?></button>
            </div>
          </div>
        </div>
        <!-- The Modal -->
      <div id="myModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
          <span class="close">&times;</span>
          <div id="popup_content"> Loading.......</div>
        </div>
      </div>
        <form method="post" action="" enctype="multipart/form-data" id="form-order">
          <div class="table-responsive">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <td style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);" /></td>
                  <td class="text-right"><?php if ($sort == 'o.order_id') { ?>
                    <a href="<?php echo $sort_order; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_order_id; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_order; ?>"><?php echo $column_order_id; ?></a>
                    <?php } ?></td>
                  <td class="text-left"><?php echo $column_flora_order_id; ?></td>
                  <td class="text-left"><?php if ($sort == 'customer') { ?>
                    <a href="<?php echo $sort_customer; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_customer; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_customer; ?>"><?php echo $column_customer; ?></a>
                    <?php } ?></td>
                  <td class="text-left"><?php if ($sort == 'order_status') { ?>
                    <a href="<?php echo $sort_status; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_status; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_status; ?>"><?php echo $column_status; ?></a>
                    <?php } ?></td>
                  <td class="text-left">
                    <?php echo $column_flora_status; ?>
                  </td>
                  <td class="text-right"><?php if ($sort == 'o.total') { ?>
                    <a href="<?php echo $sort_total; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_total; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_total; ?>"><?php echo $column_total; ?></a>
                    <?php } ?></td>
                  <td class="text-left"><?php if ($sort == 'o.date_added') { ?>
                    <a href="<?php echo $sort_date_added; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_date_added; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_date_added; ?>"><?php echo $column_date_added; ?></a>
                    <?php } ?></td>
                  <td class="text-left"><?php if ($sort == 'o.date_modified') { ?>
                    <a href="<?php echo $sort_date_modified; ?>" class="<?php echo strtolower($order); ?>"><?php echo $column_date_modified; ?></a>
                    <?php } else { ?>
                    <a href="<?php echo $sort_date_modified; ?>"><?php echo $column_date_modified; ?></a>
                    <?php } ?></td>
                  <td class="text-right"><?php echo $column_action; ?></td>
                </tr>
              </thead>
              <tbody>
                <?php if ($orders) { ?>
                <?php foreach ($orders as $order) { ?>
                <tr>
                  <td class="text-center"><?php if (in_array($order['order_id'], $selected)) { ?>
                    <input type="checkbox" name="selected[]" value="<?php echo $order['order_id']; ?>" checked="checked" />
                    <?php } else { ?>
                    <input type="checkbox" name="selected[]" value="<?php echo $order['order_id']; ?>" />
                    <?php } ?>
                    <input type="hidden" name="shipping_code[]" value="<?php echo $order['shipping_code']; ?>" /></td>
                  <td class="text-right"><?php echo $order['order_id']; ?></td>
                  <td class="text-right"><?php echo $order['flora_order_id']; ?></td>
                  <td class="text-left"><?php echo $order['customer']; ?></td>
                  <td class="text-left"><?php echo $order['order_status']; ?></td>
                  <td class="text-left"><?php echo $order['flora_status']; ?></td>
                  <td class="text-right"><?php echo $order['total']; ?></td>
                  <td class="text-left"><?php echo $order['date_added']; ?></td>
                  <td class="text-left"><?php echo $order['date_modified']; ?></td>
                  <td class="text-right">
                    <?php if $order['flora_order_id'] { ?>
                      <a href="<?php echo $order['export']; ?>" data-toggle="tooltip" title="<?php echo $button_export; ?>" class="btn btn-default"><i class="fa fa-upload"></i> Export on Flora</a>
                    <?php }else{ ?>
                      <a href="<?php echo $order['export']; ?>" data-toggle="tooltip" title="<?php echo $button_export; ?>" class="btn btn-success"><i class="fa fa-upload"></i> Export on Flora</a>
                    <?php } ?>
                    <a href="<?php echo $order['view']; ?>" data-toggle="tooltip" title="<?php echo $button_view; ?>" class="btn btn-info"><i class="fa fa-eye"></i></a>
                    <a href="<?php echo $order['edit']; ?>" data-toggle="tooltip" title="<?php echo $button_edit; ?>" class="btn btn-primary"><i class="fa fa-pencil"></i></a>
                    <a  <?php if($order['checkout_status'] == 'Complete'){ ?> onclick="shipView('<?php echo $order['flora_order_id']; ?>')" <?php }else {?> onclick="alert('Shipment not available.')" <?php } ?> data-toggle="tooltip" title="<?php echo $button_ship; ?>" class="btn btn-info"><i class="fa fa-truck"></i></a>
                    <a href="<?php echo $order['delete']; ?>" id="delete_button" data-toggle="tooltip" title="<?php echo $button_delete; ?>" class="btn btn-danger"><i class="fa fa-trash-o"></i></a>
                  </td>
                </tr>
                <?php } ?>
                <?php } else { ?>
                <tr>
                  <td class="text-center" colspan="8"><?php echo $text_no_results; ?></td>
                </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </form>
        <div class="row">
          <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
          <div class="col-sm-6 text-right"><?php echo $results; ?></div>
        </div>
      </div>
    </div>
  </div>
  <script type="text/javascript"><!--

$('.submit_on_enter').keydown(function(event) {
    // enter has keyCode = 13, change it if you want to use another button
    if (event.keyCode == 13) {
      filter();
      return false;
    }
  });

$('#button-filter').on('click', function() {
  filter();
});

function filter(){
	url = 'index.php?route=cedflora/order&<?php echo $session_token_key; ?>=<?php echo $session_token; ?>';

	var filter_order_id = $('input[name=\'filter_order_id\']').val();

	if (filter_order_id) {
		url += '&filter_order_id=' + encodeURIComponent(filter_order_id);
	}

  var filter_flora_order_id = $('input[name=\'filter_flora_order_id\']').val();

  if (filter_flora_order_id) {
    url += '&filter_flora_order_id=' + encodeURIComponent(filter_flora_order_id);
  }

	var filter_customer = $('input[name=\'filter_customer\']').val();

	if (filter_customer) {
		url += '&filter_customer=' + encodeURIComponent(filter_customer);
	}

	var filter_order_status = $('select[name=\'filter_order_status\']').val();

	if (filter_order_status != '*') {
		url += '&filter_order_status=' + encodeURIComponent(filter_order_status);
	}

	var filter_total = $('input[name=\'filter_total\']').val();

	if (filter_total) {
		url += '&filter_total=' + encodeURIComponent(filter_total);
	}

	var filter_date_added = $('input[name=\'filter_date_added\']').val();

	if (filter_date_added) {
		url += '&filter_date_added=' + encodeURIComponent(filter_date_added);
	}

	var filter_date_modified = $('input[name=\'filter_date_modified\']').val();

	if (filter_date_modified) {
		url += '&filter_date_modified=' + encodeURIComponent(filter_date_modified);
	}

	location = url;
}
//--></script> 
  <script type="text/javascript"><!--
$('input[name=\'filter_customer\']').autocomplete({
	'source': function(request, response) {
		$.ajax({
			url: 'index.php?route=customer/customer/autocomplete&<?php echo $session_token_key; ?>=<?php echo $session_token; ?>&filter_name=' +  encodeURIComponent(request),
			dataType: 'json',
			success: function(json) {
				response($.map(json, function(item) {
					return {
						label: item['name'],
						value: item['customer_id']
					}
				}));
			}
		});
	},
	'select': function(item) {
		$('input[name=\'filter_customer\']').val(item['label']);
	}
});

function fetchOrder(upload_object){
  date = $('#after_date').val();
  console.log(date);
  $.ajax({
      url: 'index.php?route=cedflora/order/fetchOrder&<?php echo $session_token_key; ?>=<?php echo $session_token; ?>',
      type: 'post',
      data : {'create_after_date' : date},
      dataType: 'json',
      beforeSend: function() {
          $(upload_object).attr('disabled', true);
          $(upload_object).after('<span class="cedflora-loading fa fa-spinner" style="margin-left:2px"></span>');
      },
      complete: function() {
          $(upload_object).attr('disabled', false);
          $('.cedflora-loading').remove();
      },
      success: function(json) {
          if (json['error']) {
              alert(json['error']);
          }

          if (json['success']) {
              location = json['reload'].replace(/&amp;/g, '&');
          }
      },
      error: function(xhr, ajaxOptions, thrownError) {
          alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      }
    });
}

function shipView(order_id){
   modal.style.display = "block";
  html = '<div class="form-group">';
  html += '<label class="control-label" for="input-total">Flora Order Id :</label>\
                <input type="text" name="flora_order_id" value="'+order_id+'" id="input-order_id" class="form-control" disabled />';
  html += '</div>';
  html += '<div class="form-group">';
  html += '<label class="control-label" for="input-tracking_no">Tracking Id :</label>\
                <input type="text" name="tracking_no" value="" placeholder="Tracking Id" id="input-tracking_no" class="form-control" />';
  html += '</div>';
  html += '<div class="form-group">\
                <label class="control-label" for="input-total">Carier :</label>\
                <select name="filter_order_status" id="input-carrier" class="form-control">\
                  <?php foreach ($carriers as $carrier) { ?>
                  <option value="<?php echo $carrier; ?>"><?php echo $carrier; ?></option>\
                  <?php } ?>
                </select>\
              </div>';
  html += '<div class="form-group"><div class="row">\
            <div class="col-sm-6">\
  <button type="button" onclick="ship(\''+order_id+'\');" data-toggle="tooltip" class="btn btn-primary form-control"><i class="fa fa-truck"></i> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Ship &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp <i class="fa fa-truck"></i></button></div><div class="col-sm-6">\
    <button class="btn btn-default form-control" onclick="cancel();">Cancel</button>\
  </div></div></div>';
  $('#popup_content').html(html);

}

function ship(order_id){
  modal.style.display = "none";
  tracking_no = $("#input-tracking_no").val();
  carrier = $("#input-carrier").val();
  if(!tracking_no){
    alert("Please Fill the Tracking no");
  }else{
    $.ajax({
        url: 'index.php?route=cedflora/order/ship&<?php echo $session_token_key; ?>=<?php echo $session_token; ?>',
        type: 'post',
        data : {'order_id': order_id, 'tracking_no' : tracking_no, 'carrier' : carrier},
        dataType: 'json',
        success: function(json) {
          if (json['success']) {
            location = json['reload'].replace(/&amp;/g, '&');
          }else{
            html = json['message'];
            modal.style.display = "block";
            $('#popup_content').html(html);
          }
        },
        error: function(xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
      });
  }
}

$("#delete_button").click(function(event){
  var permission = confirm('Are you Sure?');
  if(!permission){
    event.preventDefault();
  }
});

function cancel(){
  modal.style.display = "none";
}
//--></script> 
  <script src="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
  <link href="view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css" type="text/css" rel="stylesheet" media="screen" />
  <script type="text/javascript"><!--
$('.date').datetimepicker({
	pickTime: false
});
//--></script>
<style type="text/css">
  /* The Modal (background) */
.modal {
    display: none; /* Hidden by default */
    position: fixed; /* Stay in place */
    z-index: 10; /* Sit on top */
    left: 0;
    top: 0;
    width: 100%; /* Full width */
    height: 100%; /* Full height */
    overflow: auto; /* Enable scroll if needed */
    background-color: rgb(0,0,0); /* Fallback color */
    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
}

/* Modal Content/Box */
.modal-content {
    background-color: #fefefe;
    margin: 15% auto; /* 15% from the top and centered */
    padding: 20px;
    border: 1px solid #888;
    width: 50%; /* Could be more or less, depending on screen size */
}

/* The Close Button */
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
}

.close:hover,
.close:focus {
    color: black;
    text-decoration: none;
    cursor: pointer;
}
</style>
<script type="text/javascript">
var modal = document.getElementById('myModal');
var span = document.getElementsByClassName("close")[0];
span.onclick = function() {
    modal.style.display = "none";
    $("#popup_content").html('Loading........');
}

$('body').click(function(event) {
  if ($(event.target).is('#myModal')) {
    modal.style.display = "none";
  }
});
</script>
</div>
<?php echo $footer; ?> 