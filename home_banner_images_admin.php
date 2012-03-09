<?php
global $wpdb;
include_once('home_banner_images.class.php');
include_once('pagination.class.php');
$images = new home_banner_images();
$pag = new paginationClass();

// Install the required database table if not present
$images->init_home_banner_images();

// Update status
if( isset($_GET['status_id']) && $_GET['status_id'] )
{
	$id = (int)$_GET['status_id'];
	$post = $images->toggle_status( $id );
}

// Update ordering
if( isset($_GET['ids']) && $_GET['ids'] )
{
	$ids = (string)$_GET['ids'];
	$images->order_records( $ids );
}

$id = ( isset($_GET['id']) && $_GET['id'] ) ? (int)$_GET['id'] : false;
$submit_button_text = ( isset($_GET['id']) && $_GET['id'] ) ? 'Update Record' : 'Upload Image';
$delete_id = ( isset($_GET['delete_id']) && $_GET['delete_id'] ) ? (int)$_GET['delete_id'] : false;

// Delete record
if( isset($_GET['delete_id']) && ($_GET['delete_id']) )
	$post = $images->home_banner_image_delete( $delete_id );

// Add a new record
if( isset($_POST['add']) && $_POST['add'] )
	$post = $images->home_banner_images_save_postdata();

// Edit a record
if( isset($_POST['edit']) && $_POST['edit'] )
{
	$post = $images->home_banner_images_save_postdata();
	$id = $post['id'];
}

$records = ( (isset($id) && $id) ) ? $images->get_home_banner_image( $id ) : $images->get_home_banner_images();

#$images->dump($records);

$post_description = false;

if( isset($id) && $id )
{
	$post_description = $records->description;
}
if( isset($post['message']) && ($post['message'] == 'error') )
{
	$post_description = $post['description'];
}

$post_url = false;

if( isset($id) && $id )
{
	$post_url = $records->url;
}
if( isset($post['message']) && ($post['message'] == 'error') )
{
	$post_url = $post['url'];
}

$upload_label = ( isset($id) && $id ) ? 'Replace This Image' : 'Upload a New Image';
?>

<div class="wrap">
	<h2>Home Page Banner Images</h2>
	
	<?php if( isset($id) && $id ) { ?>
		<p><a href="themes.php?page=home_banner_images">Back to Browse</a></p>
	<?php } ?>
	
	<?php if( isset($post['message']['type']) && $post['message']['type'] ) { ?>
			<div id="message" class="<?php echo $post['message']['type']; ?>"><?php echo $post['message']['text']; ?></div>
	<?php } ?>
	
	<div class="home_banner_image_upload_form" style="margin: 20px 0 20px 0;">
		<?php if( isset($id) && $id ) { ?>
			<h3>Current Image</h3>
			<p><em>(Click the image for a larger view)</em></p>
			<p><a href="../wp-content/uploads/home_banner_images/<?php echo $records->image_file_name; ?>" 
				title="<?php echo $post_description; ?>" 
				class="thickbox"><img src="../wp-content/uploads/home_banner_images/<?php echo $records->image_file_name; ?>" width="275"></a></p>
		<?php } ?>
		<p><strong>Image dimensions must be exactly <?php echo $images->image_width; ?> (h) x <?php echo $images->image_height; ?> (w) pixels</strong></p>
		<form method="post" enctype="multipart/form-data" action="themes.php?page=home_banner_images">
			<div class="input-area">
				<div class="input-area-left">
					<?php echo $upload_label; ?>
				</div>
				<div class="input-area-right">
					 <input type="file" name="home_banner_image" id="home_banner_image" />
				</div>
			</div>
			<div class="input-area">
				<div class="input-area-left">
					Description <em>(50 character limit)</em>
				</div>
				<div class="input-area-right">
					<input type="input" 
						name="description" 
						id="description" 
						class="text_input" 
						maxlength="50" 
						value="<?php echo $post_description; ?>" />
				</div>
			</div>
			<div class="input-area">
				<div class="input-area-left">
					URL <em>(Example: instruments/details)</em>
				</div>
				<div class="input-area-right">
					<input type="input" 
						name="url" 
						id="url" 
						class="text_input" 
						value="<?php echo $post_url; ?>" />
					<?php
					/*
					<textarea id="url"></textarea>
					
					the_editor( $post_url, 
						$id = 'url', 
						$prev_id = 'title', 
						$media_buttons = false, 
						$tab_index = 2, 
						$extended = true );
					*/
					#wp_tiny_mce(true);
					
					#the_editor($post_url, 'url');
					?>
				</div>
			</div>
			<?php if( isset($records->id) && $records->id ) { ?>
				<input type="hidden" name="id" id="id" value="<?php echo $records->id; ?>" />
			<?php } ?>
			<?php if( isset($records->image_file_name) && $records->image_file_name ) { ?>
				<input type="hidden" 
					name="image_file_name" 
					id="image_file_name" 
					value="<?php echo $records->image_file_name; ?>" />
			<?php } ?>
			<div style="clear: both;">
				<input type="button" 
					name="cancel" 
					value="Cancel" 
					onclick="javascript:window.location = 'themes.php?page=home_banner_images'" />
				<input type="submit" 
					name="<?php echo ( isset($id) && ($id) ) ? 'edit' : 'add'; ?>" 
					value="<?php echo $submit_button_text; ?>" />
			</div>
		</form>
	</div>
	
	<?php if( empty($id) && !$id ) { ?>
	
	<?php
	// Get the current page
	$this_page = ( isset($_GET['p']) && $_GET['p'] > 0 ) ? (int)$_GET['p'] : 1;
	// Records per page
	$per_page = 25;
	// Total Pages
	$total_page = ceil($records['pagination_count']/$per_page);
	
	// Set the pagination variable values
	$pag->Items($records['pagination_count']);
	$pag->limit($per_page);
	$pag->target("themes.php?page=home_banner_images");
	$pag->currentPage($this_page);
	
	$list_start = ($this_page - 1)*$per_page;
	if($list_start >= $records['pagination_count'])  // Start of the list should be less than pagination count
	    $list_start = ($records['pagination_count'] - $per_page);
	if($list_start < 0) // List start cannot be negative
	    $list_start = 0;
	#$list_end = ($this_page * $per_page) - 1; (WTF?)
	$list_end = ($this_page * $per_page);
	?>
	
	<p><em><strong>Note:</strong> There can only be 5 active home page banner images. </em> 
		<span class="ordering_message"></span></p>
	
		<table class="widefat" id="home_banner_images_data">
			<thead>
				<tr>
					<th>ID</th>
					<th>Image File Name</th>
					<th>Description</th>
					<th>Created Date</th>
					<th>Status</th>
					<th>Manage</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>ID</th>
					<th>Image File Name</th>
					<th>Description</th>
					<th>Created Date</th>
					<th>Status</th>
					<th>Manage</th>
				</tr>
			</tfoot>
			<tbody>
			<?php $i = 1; ?>
				<?php foreach($records['data'] as $record) { ?>
				<tr id="<?php echo $record->id; ?>" title="Drag to re-order">
					<td><?php echo $record->id; ?></td>
					<td><a href="../wp-content/uploads/home_banner_images/<?php echo $record->image_file_name; ?>" 
						title="<?php echo strip_tags($record->description); ?>" 
						class="thickbox"><?php echo htmlspecialchars($record->image_file_name); ?></a></td>
					<td><?php echo htmlspecialchars($record->description); ?></td>
					<td><?php echo $record->created_date; ?></td>
					<td id="status_id_<?php echo $record->id; ?>">
						<a href="?page=home_banner_images&status_id=<?php echo $record->id; ?>" 
							title="Toggle the status of this record"><?php echo $record->status; ?></a>
					</td>
					<td><a href="?page=home_banner_images&id=<?php echo $record->id; ?>" title="Edit this record">edit</a> | 
						<a href="javascript:confirmation(<?php echo $record->id; ?>);" title="Delete this record">delete</a></td>
				</tr>
				<?php $i++; ?>
				<?php } ?>
			</tbody>
		</table>
		<?php // Display the pagination links ?>
        <div class="tablenav">
            <div class="alignleft actions">
                <?php // <input type="submit" class="button-secondary" value="Bulk Delete" /> ?>
            </div>
            <div class="tablenav-pages">
                <span class="displaying-num"><?php echo $records['pagination_count']; ?> items</span>
                <?php $pag->show(); ?>
            </div>
        </div>
        
	<?php } ?>
	
</div>

<script>
jQuery(document).ready(function() {
	
	jQuery("#message").delay(3800).slideUp("fast");
	
	// Make a nice striped effect on the table
	jQuery( "#home_banner_images_data tr:odd" ).addClass( "odd_row" );

	// Initialize the second table specifying a dragClass and an onDrop function that will display an alert
	// More info: http://www.isocra.com/2008/02/table-drag-and-drop-jquery-plugin/
	
	
	jQuery("#home_banner_images_data").tableDnD({
	    onDragClass: "dragging",
	    onDrop: function(table, row) {
	    	
			var sort_array = new Array;
	
	    	jQuery("table.widefat tbody tr").each(function(){
				sort_array.push(jQuery(this).attr('id'));
			});
			
			var data_string = sort_array.join('_');
			
			jQuery.ajax({
				type: "GET", 
				url: "<?php echo bloginfo('wpurl'); ?>/wp-admin/themes.php", 
				context: document.body, 
				data: "page=home_banner_images&ids="+data_string, 
				success: function(data) {
					jQuery(".ordering_message").fadeIn("fast").html('Successfully re-ordered records.').delay(3000).fadeOut("slow");
				}
			});
	    	
	    	//serialized_data = jQuery.tableDnD.serialize();
	    	//order_records(serialized_data);
	    }
	});
});
</script>