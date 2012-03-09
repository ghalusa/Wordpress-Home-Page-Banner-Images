<?php
class home_banner_images
{
	
	public function __construct()
	{
		$this->uploads = ( wp_upload_dir() );
		$this->target_path = ( $this->uploads['basedir'].'/home_banner_images/' );
		$this->image_width = '688';
		$this->image_height = '343';
		global $wpdb;
	}
	
	
	public function dump($data = false, $die = true, $ip_address=false)
	{
		if(!$ip_address || $ip_address == $_SERVER["REMOTE_ADDR"])
		{
			echo '<pre>';
			var_dump($data);
			echo '</pre>';
	
			if($die) die();
		}
	}
	
	
	public function init_home_banner_images()
	{	
		global $wpdb;
		
		$result = mysql_list_tables( DB_NAME );
		$current_table = array();
		while( $row = mysql_fetch_row($result) )
		{
			$current_tables[] = $row[0];
		}
		$myNewDatabaseTable = $wpdb->prefix . 'home_page_banner_images';
		if( !in_array($myNewDatabaseTable, $current_tables) )
		{
	
			mysql_query("
			CREATE TABLE IF NOT EXISTS `" . $myNewDatabaseTable . "` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`ordering` int(11) NOT NULL,
			`image_file_name` varchar(255) NOT NULL,
			`description` varchar(50) NOT NULL,
			`url` varchar(255) DEFAULT NULL,
			`active` tinyint(1) NOT NULL DEFAULT '1',
			`created_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`)
			);
			");
		}
		
		if ( !file_exists($this->target_path) ) {
		mkdir( $this->target_path, 0775 );
		}
		
	}
	
	
	public function home_banner_images_save_postdata()
	{
		global $wpdb;
		
		$data = array();
		$data['message'] = array();
		$fileArray = array();
		$record_id = false;
		
		if( isset($_GET['id']) && $_GET['id'] )
			$record_id = (int)$_GET['id'];
		if( isset($_POST['id']) && $_POST['id'] )
			$record_id = (int)$_POST['id'];
			
		$data = $_POST;
		$data['file'] = $_FILES['home_banner_image'];
		$basename = '';
		
		if( empty($data['description']) )
		{
			$data['message']['type'] = 'error';
			$data['message']['text'] = 'Please fill in the description. You will need to re-enter the file.';
			$description = '';
		}
		else
		{
			$description = $data['description'];
		}
		
		if( empty($data['file']['name']) && !isset($_POST['edit']) )
		{
			$data['message']['type'] = 'error';
			$data['message']['text'] = 'Please upload an image file.';
		}
		
		if( isset($data['file']['tmp_name']) && !empty($data['file']['tmp_name']) )
		{
			list($width, $height, $type, $attr) = getimagesize($data['file']['tmp_name']);
			
			if( ($width != $this->image_width) || ($height != $this->image_height) )
			{
				$data['message']['type'] = 'error';
				$data['message']['text'] = 'Please make sure your image is exactly '.$this->image_width.
						' (h) x '.$this->image_height.' (w).';
			}
		}
		
		if( isset($data['file']['name']) && $data['file']['name'] && 
			!$this->isAllowedExtension($data['file']['name']) )
		{
			$data['message']['type'] = 'error';
			$data['message']['text'] = 'Please upload a valid image file type.';
		}
		
		// If a file by the same name exists and is a new insert
		if( isset($data['file']['name']) && $data['file']['name'] && 
			( $data['file']['name'] == $data['image_file_name'] ) && 
			( isset($_POST['add']) && $_POST['add'] ) )
		{
			$data['message']['type'] = 'error';
			$data['message']['text'] = 'A file with the same name already exists. Please rename your file and try again.';
		}
		
		
		if( !isset($data['message']) )
		{
			// Upload if a file is present
			if( isset($data['file']['name']) && !empty($data['file']['name']) ) 
			{
				// Check for existing image file
				$existing_images_array = array();
				// Get all records ( get_home_banner_images() ), and create an array of existing image files
				$records = $this->get_home_banner_images();
				foreach( $records['data'] as $record )
				{
					$existing_images_array[] = $record->image_file_name;
				}
				
				// Is there an existing image file with the same name? (returns boolean, true or false)
				$image_exists = in_array($data['file']['name'], $existing_images_array);
				
				// If there is an existing image file with the same name, extract the file extension to append to new file name
				$image_extension = $image_exists ? end(explode(".", $data['file']['name'])) : "";
				
				// If is an existing image file with the same name, rename the file
				$data['file']['name'] = ($image_exists) ? 
					str_replace( '.'.$image_extension, '_'.time().'.'.$image_extension, $data['file']['name'] ) : 
					$data['file']['name'];
				
				// Format the image file name in an acceptable format
				$basename = basename( $data['file']['name'] );
				$basename = str_replace(' ', '_', $basename);
				$basename = str_replace('-', '_', $basename);
				$basename = strtolower($basename);
				$target_path = $this->target_path . $basename;
			
				if( isset($data['file']['tmp_name']) && $data['file']['tmp_name'] )
				{
					move_uploaded_file($data['file']['tmp_name'], $target_path);
				}
				if( isset($data['image_file_name']) && $data['file']['name'] != $data['image_file_name'] )
				{
					unlink($this->target_path.$data['image_file_name']);
				}
				
			}
			else
			{
				// If a file is not present...
				$basename = $data['image_file_name'];
			}
			
			
			// Insert a new record into the database
			if( isset($_POST['add']) && $_POST['add'] )
			{
				
				// Get the current count of active records.
				// If it's 5 or more, then set "active" to false
				// and set a message that it has been set to inactive
				$active_total = $this->get_active_records();
				$active = ( ($active_total['active_count'] >= 5) && !isset($_POST['edit']) ) ? 0 : 1;
				if( !$active )
				{
					$success_message = 'The new record has been set to inactive since there are 
						currently 5 active records. ';
				}
				
				$sqlQuery = "INSERT INTO " . 
				$wpdb->prefix . "home_page_banner_images(image_file_name, description, url, active) 
				VALUES('$basename', '$description', '".$data['url']."', '$active')";
				$success_message .= 'Record successfully added.';
				
				#$this->dump($sqlQuery);
			}
			
			// Update an existing record in the database
			if( isset($_POST['edit']) && $_POST['edit'] )
			{
				$sqlQuery = "UPDATE `" . 
				$wpdb->prefix . "home_page_banner_images`" .
				"SET `image_file_name` = '$basename', `description` = '$description', `url` = '".$data['url']."'
				WHERE `id` = '$record_id'";
				$success_message = 'Record successfully updated.';
			}
			
			// Execute one of the queries
			$wpdb->query($sqlQuery);
			
			$data['message']['type'] = 'updated';
			$data['message']['text'] = $success_message;
			
			return $data;
			header("Location: themes.php?page=home_banner_images");
			exit;
		}
		else
		{
			// If there are errors...
			return $data;
			header("Location: themes.php?page=home_banner_images&id=".$record_id);
			exit;
		}

		return $data;
	}
	
	
	private function isAllowedExtension( $fileName ) 
	{
		$allowedExtensions = array("png", "gif", "jpg", 'jpeg');
		return in_array(end(explode(".", $fileName)), $allowedExtensions);
	}
	
	
	public function get_home_banner_images( $list_start = false, $list_end = false )
	{
		global $wpdb;
		
		$limit = ($list_start && $list_end) ? 'LIMIT '.$list_start.' '.$list_end : '';
	    $results['data'] = $wpdb->get_results($wpdb->prepare("SELECT CASE active 
			WHEN true THEN 'active' 
			WHEN false THEN 'inactive' END as status,
			id, ordering, image_file_name, description, url, created_date
			FROM `".$wpdb->prefix."home_page_banner_images` 
			ORDER BY `ordering` ASC ".$limit));
		// Get the number of records in the database table
		$results['pagination_count'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(`id`) 
		FROM `" . $wpdb->prefix . "home_page_banner_images`"));
		
	    return $results;
	}
	
	
	public function get_home_banner_image( $id )
	{
		global $wpdb;
	    $result = $wpdb->get_row("SELECT CASE active 
			WHEN true THEN 'active' 
			WHEN false THEN 'inactive' END as status,
			id, image_file_name, description, url, created_date
			FROM `".$wpdb->prefix."home_page_banner_images` 
			WHERE `id` = ".$id);
	    return $result;
	}
	
	
	public function toggle_status( $id = false )
	{
		global $wpdb;
		
		$toggle_value = 1;
	    $result = $wpdb->get_row("SELECT image_file_name, active
			FROM `".$wpdb->prefix."home_page_banner_images` 
			WHERE `id` = ".$id);
		$toggle_value = ( $result->active == '0' ) ? '1' : '0';
		
		#$this->dump($toggle_value);
		
		// Get the number of records which are currently active
		$active_total = $this->get_active_records();
		
		if( ($result->active == 0) && ($active_total['active_count'] >= 5) )
		{
			$data['message']['type'] = 'error';
			$data['message']['text'] = 'There is a limit of 5 active records. 
				Please deactivate one record, then reactivate as desired.';
		}
		else
		{
			$returned_text = ( $result->active == '0') ? 'active' : 'inactive';
			$sqlQuery = "UPDATE `" . 
						$wpdb->prefix . "home_page_banner_images`" .
						"SET `active` = '$toggle_value'
						WHERE `id` = '$id'";
			$wpdb->query($sqlQuery);
			
			$data['message']['type'] = 'updated';
			$data['message']['text'] = 'The status for record ID #'.$id.' - <strong>'.$result->image_file_name.'</strong> has been set to '.$returned_text.'.';
		}
	    return $data;
	}
	
	
	public function get_active_records()
	{
		global $wpdb;
		
		// Get the number of records which are currently active
		$data['active_count'] = $wpdb->get_var($wpdb->prepare("SELECT COUNT(`id`) 
		FROM `" . $wpdb->prefix . "home_page_banner_images`
		WHERE active = 1"));
		
		return $data;
	}
	
	
	public function home_banner_image_delete( $id = false )
	{
		global $wpdb;
		
		$record = $this->get_home_banner_image( $id );
		
		unlink($this->target_path.$record->image_file_name);
		
		$sqlQuery = "DELETE FROM `" . 
					$wpdb->prefix . "home_page_banner_images` 
					WHERE `id` = '$id'";
		$wpdb->query($sqlQuery);
		
		$data['message']['type'] = 'updated';
		$data['message']['text'] = 'Record #'.$record->id.', '.$record->image_file_name.' successfully deleted.';
		
	    return $data;
	    
	    header("Location: themes.php?page=home_banner_images");
		exit;
	}
	
	
	public function order_records( $ids )
	{
		global $wpdb;
		
		$data = explode('_', $ids);
		
		$i = 1;
		foreach($data as $id)
		{
			$sqlQuery = "UPDATE `" . 
						$wpdb->prefix . "home_page_banner_images`" .
						"SET `ordering` = '$i'
						WHERE `id` = '$id'";
			$wpdb->query($sqlQuery);
		
			$i++;
		}
		
	}
		
}
?>