<?php include HABARI_PATH . '/system/admin/header.php'; ?>
<div class="publish">
<div class="container">

	<?php if(Session::has_messages()) {Session::messages_out();} ?>
	
		
		<form name="form_options" id="form_options" action="<?php URL::out('admin', 'page=blogroll'); ?>" method="post">
		
			<div class="container">
				<p class="span-10 column first"><label for="name" class="incontent">Blog Name</label>
				<input type="text" id="name" name="name" size="100%" value="<?php echo $name; ?>"  class="styledformelement"></p>
				
				<p class="span-10 column last"><label for="url" class="incontent">Blog URL</label>
				<input type="text" id="url" name="url" size="100%" value="<?php echo $url; ?>" class="styledformelement"></p>
			</div>

			<p><label for="description" class="incontent">Description</label>
			<textarea id="description" name="description" rows="20" cols="114" class="styledformelement resizable"><?php echo $description; ?></textarea></p>
			
			<div class="container">
				<p class="span-10 column first"><label for="feed" class="incontent">Feed URL</label>
				<input type="text" id="feed" name="feed" size="100%" value="<?php echo $feed; ?>" class="styledformelement"></p>
				
				<p class="span-10 column last"><label for="owner" class="incontent">Owner Name</label>
				<input type="text" id="owner" name="owner" size="100%" value="<?php echo $owner; ?>" class="styledformelement"></p>
			</div>

			<div id="formbuttons" class="container">
				<p><?php if ( $id ) : ?><input type="hidden" id="id" name="id" value="<?php echo $id; ?>"><?php endif; ?>
				<input type="submit" id="submit_options" name="submit_options" value="Save"></p>
			</div>
		</form>
	
	
</div>
</div>

<?php include HABARI_PATH . '/system/admin/footer.php'; ?>
