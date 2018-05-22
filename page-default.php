<div>
<form class="form-horizontal" method="post" enctype="multipart/form-data" action="./?chain=<?php echo html($_GET['chain'])?>&page=list">
		<div class="col-sm-offset-2 col-sm-9">
			<input class="form-control" name="name" id="name" placeholder="Nhập tên văn bằng/chứng chỉ để kiểm tra" type="text" value="">
			<lable> Hoặc Chọn file để kiểm tra:</lable>
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
                echo 'File Upload Is Error';
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
		
	if (@$_POST['check']) {
		$check='';
		if (isset($_FILES['fileToUpload']))
		{
			if ($_FILES['fileToUpload']['error'] > 0)
            {
                
            }
            else{
				$result = pdf2text($file_name);
				$len = strlen($result);
				for($i = 0; $i < $len; $i++)
				{
					if ($result[$i] == ';')
						break;
					if($i % 2 == 1)
						$check .= $result[$i];
				}	
            }
		}
		
		$name_issue = $check;
		
		if($_POST['name'] != NULL)
			$name_issue = $_POST['name'];
			
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
?>

			<div class="row">
<?php
//check admin
if(count($issueaddresses) > 0){
?>
				<div class="col-sm-6">
<?php
}else{
?>
				<div class="col-sm-10">
<?php
}
?>
					<h3>Kết quả</h3>
			
<?php



	if (no_displayed_error_result($listassets, multichain('listassets', $name_issue, true))) {

		foreach ($listassets as $asset) {
			$name=$asset['name'];
			$issuer=$asset['issues'][0]['issuers'][0];
?>
						<table class="table table-bordered table-condensed table-break-words <?php echo ($success && ($name==@$_POST['name'])) ? 'bg-success' : 'table-striped'?>">
							<tr>
								<th style="width:30%;">Tên chứng chỉ</th>
								<td><?php echo html($name)?> <?php echo $asset['open'] ? '' : '(closed)'?></td>
							</tr>
							<tr>
								<th>School Address</th>
								<td class="td-break-words small"><?php echo format_address_html($issuer, @$keymyaddresses[$issuer], $labels)?></td>
							</tr>
<?php
			$details=array();
			$detailshistory=array();
			
			foreach ($asset['issues'] as $issue)
				foreach ($issue['details'] as $key => $value) {
					$detailshistory[$key][$issue['txid']]=$value;
					$details[$key]=$value;
				}
			
			if (count(@$detailshistory['@file'])) {
?>
							<tr>
								<th>File:</th>
								<td><?php
								
				$countoutput=0;
				$countprevious=count($detailshistory['@file'])-1;

				foreach ($detailshistory['@file'] as $txid => $string) {
					$fileref=string_to_fileref($string);
					if (is_array($fileref)) {
					
						$file_name=$fileref['filename'];
						$file_size=$fileref['filesize'];
						
						if ($countoutput==1) // first previous version
							echo '<br/><small>('.$countprevious.' previous '.(($countprevious>1) ? 'files' : 'file').': ';
						
						echo '<a href="./download-file.php?chain='.html($_GET['chain']).'&txid='.html($txid).'&vout='.html($fileref['vout']).'">'.
							(strlen($file_name) ? html($file_name) : 'Download').
							'</a>'.
							( ($file_size && !$countoutput) ? html(' ('.number_format(ceil($file_size/1024)).' KB)') : '');
						
						$countoutput++;
					}
				}
				
				if ($countoutput>1)
					echo ')</small>';
								
								?></td>
							</tr>	
<?php
			}
			
			foreach ($details as $key => $value) {
				if ($key=='@file')
					continue;
?>
							<tr>
								<th><?php echo html($key)?></th>
								<td><?php echo html($value)?><?php
								
				if (count($detailshistory[$key])>1)
					echo '<br/><small>(previous values: '.html(implode(', ', array_slice(array_reverse($detailshistory[$key]), 1))).')</small>';
				
								?></td>
							</tr>
<?php
			}
?>							
						</table>
<?php
		}
	}
?>
				</div>
				</div>
			</div>
