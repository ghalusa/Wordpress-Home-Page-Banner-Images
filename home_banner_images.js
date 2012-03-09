function confirmation( id )
{
	var answer = confirm("Are you sure you want to delete this record?");
	if( answer )
	{
		window.location = 'themes.php?page=home_banner_images&delete_id='+id;
	}
}

/*
function toggle_status( id, db_prefix )
{
	jQuery.ajax({
		type: "GET", 
		url: "http://www.goranhalusa.com/wp-admin/themes.php", 
		context: document.body, 
		data: "page=home_banner_images&record_id="+id+"&db_prefix="+db_prefix, 
		success: function(data) {
			jQuery("#status_id_"+id).html(data);
		}
	});
}
*/