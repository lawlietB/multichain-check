<div class="form-group col-sm-12 col-md-12">
	<form class="form-horizontal" method="post" enctype="multipart/form-data" action="./?chain=<?php echo html($_GET['chain'])?>&page=list" novalidate class="box">
		<div class="col-sm-offset-2 col-sm-9">
			<lable><b>Chọn file PDF để kiểm tra:</b></lable>
			<input type="file" name="fileToUpload" id="fileToUpload"><br>	
			<input class="btn btn-default" type="submit" name="check" value="Kiểm tra">
		</div>
		
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
	$_data = '';
		
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
					// if($i % 2 == 1)
					$name_issue .= $result[$i];
				}
				$i += 1;
				for($i; $i < $len; $i++)
				{
					// $data .= $result[$i];
					if ($result[$i] == ';')
						break;
					// if($i % 2 == 1)
					$data .= $result[$i];
				}	
			}
			if($name_issue == '')
				$name_issue='_error_';
		}			
		shell_exec('rm -r '.$file_name); //in linux
		shell_exec('del /f '.$file_name); //in window
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
		if($data == hash('sha256',$items[0]['data']))
			$check_data = true;
		if($check_data == false)
		{
			echo '<div class="bg-danger" style="padding:1em;">Thông tin không chính xác hoặc đây là bằng/chứng chỉ giả<br/></div>';
		}

		if ($success && $check_data) {		
?>
				
				<div class="col-sm-12 col-md-12 bg-success">
					<h3>Kết quả: <?php echo html($viewstream['name'])?> &ndash; <?php echo count($items)?> of <?php echo $countitems?> <?php echo ($countitems==1) ? 'item' : 'items'?><?php echo html($suffix)?></h3>
<?php
			$oneoutput=false;
			$items=array_reverse($items); // show most recent first
			
			foreach ($items as $item) {
				$oneoutput=true;
?>
					<table class="table table-bordered">
						<tr class="default">
							<th style="width:15%;">Nhà phát hành</th>
							<td><?php
							
				foreach ($item['publishers'] as $publisher) {
					$link='./?chain='.$_GET['chain'].'&page='.$_GET['page'].'&stream='.$viewstream['createtxid'].'&publisher='.$publisher;
					
							?><?php echo format_address_html($publisher, false, $labels, $link)?><?php
							
				}
							
							?></td>
						</tr>
						<tr class="default">
							<th>Tên</th>
							<td><a href="./?chain=<?php echo html($_GET['chain'])?>&page=<?php echo html($_GET['page'])?>&stream=<?php echo html($viewstream['createtxid'])?>&key=<?php echo html($item['key'])?>"><?php echo html($item['key'])?></a></td>
						</tr>
						<tr class="default">
							<th>Thông tin</th>
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
						<tr class="default">
							<th>Ngày thêm</th>
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