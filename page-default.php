<div class="form-group col-sm-12 col-md-12">
	<!-- <form class="form-horizontal" method="post" enctype="multipart/form-data" action="./?chain=<?php echo html($_GET['chain'])?>&page=list" novalidate class="box">
		<div class="col-sm-offset-2 col-sm-9">
			<lable><b>Chọn file PDF để kiểm tra:</b></lable>
			<input type="file" name="fileToUpload" id="fileToUpload"><br>	
			<input class="btn btn-default" type="submit" name="check" value="Kiểm tra">
		</div>
		
	</form> -->

	<form method="post" action="./?chain=<?php echo html($_GET['chain'])?>&page=list" enctype="multipart/form-data" novalidate class="box">			
		<div class="box__input">
			<svg class="box__icon" xmlns="http://www.w3.org/2000/svg" width="50" height="43" viewBox="0 0 50 43">
			<path d="M48.4 26.5c-.9 0-1.7.7-1.7 1.7v11.6h-43.3v-11.6c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v13.2c0 .9.7 1.7 1.7 1.7h46.7c.9 0 1.7-.7 1.7-1.7v-13.2c0-1-.7-1.7-1.7-1.7zm-24.5 6.1c.3.3.8.5 1.2.5.4 0 .9-.2 1.2-.5l10-11.6c.7-.7.7-1.7 0-2.4s-1.7-.7-2.4 0l-7.1 8.3v-25.3c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v25.3l-7.1-8.3c-.7-.7-1.7-.7-2.4 0s-.7 1.7 0 2.4l10 11.6z"/></svg>
			<input type="file" name="fileToUpload" id="file" class="box__file" data-multiple-caption="{count} files selected" multiple />
			<label for="file"><strong>Choose a file</strong><span class="box__dragndrop"> or drag it here</span>.</label>
			<button type="submit" class="box__button" name="check">Upload</button>
		</div>
		<input class="btn btn-default" type="submit" name="check" value="Kiểm tra">
	</form>
</div>



<?php
require_once("pdf2text.php");
$file_name = '';
    if (isset($_POST['check']))
    {
        if (isset($_FILES['fileToUpload']))
        {
            if ($_FILES['fileToUpload']['error'] > 0)
            {
            }
            else{
				move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $_FILES['fileToUpload']['name']);
				$file_name = $_FILES['fileToUpload']['name'];
            }
        }
    }
?>

<?php
	define('const_issue_custom_fields', 15);
	
	$max_upload_size=multichain_max_data_size()-512; // take off space for file name and mime type

	$success=false; // set default value

	$name_issue = '';
	$data = '';
		
	if (@$_POST['check']) {
		if (isset($_FILES['fileToUpload']))
		{
			if ($_FILES['fileToUpload']['error'] > 0)
            {
                
            }
            else{
				$result = pdf2text($file_name);
				$len = strlen($result);
				$i = 0;
				for($i; $i < $len; $i++)
				{
					if ($result[$i] == ':')
						break;
					if($i % 2 == 1)
						$name_issue .= $result[$i];
				}
				$i += 2;
				for($i; $i < $len; $i++)
				{
					if ($result[$i] == ';')
						break;
					if($i % 2 == 1)
						$data .= $result[$i];
				}	
            }
		}			
		shell_exec('rm -r '.$file_name);
	}

	$getinfo=multichain_getinfo();

	$issueaddresses=array();
	$keymyaddresses=array();
	$receiveaddresses=array();
	$labels=array();

	if (no_displayed_error_result($getaddresses, multichain('getaddresses', true))) {

		if (no_displayed_error_result($listpermissions,
			multichain('listpermissions', 'issue', implode(',', array_get_column($getaddresses, 'address')))
		))
			$issueaddresses=array_get_column($listpermissions, 'address');
		
		foreach ($getaddresses as $address)
			if ($address['ismine'])
				$keymyaddresses[$address['address']]=true;
				
		if (no_displayed_error_result($listpermissions, multichain('listpermissions', 'receive')))
			$receiveaddresses=array_get_column($listpermissions, 'address');

		$labels=multichain_labels();
	}

	define('const_max_retrieve_items', 1000);


	no_displayed_error_result($liststreams, multichain('liststreams', $name_issue, true));
	no_displayed_error_result($getinfo, multichain('getinfo'));
	
	
	if(isset($liststreams[0]))
		$viewstream=$liststreams[0];
	
	if (isset($viewstream)) {
		if (isset($_GET['key'])) {
			$success=no_displayed_error_result($items, multichain('liststreamkeyitems', $viewstream['createtxid'], $_GET['key'], true, const_max_retrieve_items));
			$success=$success && no_displayed_error_result($keysinfo, multichain('liststreamkeys', $viewstream['createtxid'], $_GET['key']));
			$countitems=$keysinfo[0]['items'];
			$suffix=' with key: '.$_GET['key'];
			
		} elseif (isset($_GET['publisher'])) {
			$success=no_displayed_error_result($items, multichain('liststreampublisheritems', $viewstream['createtxid'], $_GET['publisher'], true, const_max_retrieve_items));
			$success=$success && no_displayed_error_result($publishersinfo, multichain('liststreampublishers', $viewstream['createtxid'], $_GET['publisher']));
			$countitems=$publishersinfo[0]['items'];
			$suffix=' with publisher: '.$_GET['publisher'];
		
		} else {
			$success=no_displayed_error_result($items, multichain('liststreamitems', $viewstream['createtxid'], true, const_max_retrieve_items));
			$countitems=$viewstream['items'];
			$suffix='';
		}

		//Check data is match
		$check_data = false;
		if($data == $items[0]['data'])
			$check_data = true;

		if($check_data == false)
		{
			echo '<div class="bg-danger" style="padding:1em;">Thông tin không chính xác hoặc đây là bằng/chứng chỉ giả<br/></div>';
		}

		if ($success && $check_data) {		
?>
				
				<div class="col-sm-8">
					<h3>Kết quả: <?php echo html($viewstream['name'])?> &ndash; <?php echo count($items)?> of <?php echo $countitems?> <?php echo ($countitems==1) ? 'item' : 'items'?><?php echo html($suffix)?></h3>
<?php
			$oneoutput=false;
			$items=array_reverse($items); // show most recent first
			
			foreach ($items as $item) {
				$oneoutput=true;
?>
					<table class="table table-bordered table-condensed table-striped table-break-words">
						<tr>
							<th style="width:15%;">Publishers</th>
							<td><?php
							
				foreach ($item['publishers'] as $publisher) {
					$link='./?chain='.$_GET['chain'].'&page='.$_GET['page'].'&stream='.$viewstream['createtxid'].'&publisher='.$publisher;
					
							?><?php echo format_address_html($publisher, false, $labels, $link)?><?php
							
				}
							
							?></td>
						</tr>
						<tr>
							<th>Name</th>
							<td><a href="./?chain=<?php echo html($_GET['chain'])?>&page=<?php echo html($_GET['page'])?>&stream=<?php echo html($viewstream['createtxid'])?>&key=<?php echo html($item['key'])?>"><?php echo html($item['key'])?></a></td>
						</tr>
						<tr>
							<th>Data</th>
							<td><?php
				
				if (is_array($item['data'])) { // long data item
					if (no_displayed_error_result($txoutdata, multichain('gettxoutdata', $item['data']['txid'], $item['data']['vout'], 1024))) // get prefix only for file name
						$binary=pack('H*', $txoutdata);
					else
						$binary='';
						
					$size=$item['data']['size'];
				
				} else {
					$binary=pack('H*', $item['data']);
					$size=strlen($binary);
				}
				
				$file=txout_bin_to_file($binary);
					
				if (is_array($file))
					echo '<a href="./download-file.php?chain='.html($_GET['chain']).'&txid='.html($item['txid']).'&vout='.html($item['vout']).'">'.
							(strlen($file['filename']) ? html($file['filename']) : 'Download').
							'</a>'.' ('.number_format(ceil($size/1024)).' KB)'; // ignore first few bytes of size
				else
					echo str_replace(';','<br>',$binary);
					
							?></td>
						</tr>
						<tr>
							<th>Added</th>
							<td><?php echo gmdate('Y-m-d H:i:s', isset($item['blocktime']) ? $item['blocktime'] : $item['time'])?> GMT<?php echo isset($item['blocktime']) ? ' (confirmed)' : ''?></td>
						</tr>
					</table>
<?php
				}
				
			if (!$oneoutput)
				echo '<p>No items in stream</p>';
?>				
				</div>
				
<?php
		}
	}
?>