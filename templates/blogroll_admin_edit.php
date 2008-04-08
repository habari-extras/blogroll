<?php include HABARI_PATH . '/system/admin/header.php'; ?>
<div class="publish">
<div class="container">

	<?php if(Session::has_messages()) {Session::messages_out();} ?>
	
		
		<form name="form_options" id="form_options" action="<?php URL::out('admin', 'page=blogroll'); ?>" method="post">
		
		<div class="container pagesplitter">
			<ul class="tabcontrol tabs">
				<li class="first last"><a href="#quick-link-blogroll"><?php _e( 'Add Quick Link', 'blogroll' ); ?></a></li>
			</ul>
			<div id="quick-link-blogroll" class="splitter">
				<div class="splitterinside">
					<div class="container">
						<p class="prepend-2"><?php _e( 'Enter the URL or feed URL, other information will be "auto-discovered".', 'blogroll' ); ?></p>
						
						<div class="container publish">
							<p class="prepend-1"><label for="quick_link" class="incontent"><?php _e( 'URL', 'blogroll' ); ?></label>
							<input type="text" id="quick_link" name="quick_link" size="100%" value="" class="styledformelement"></p>
						</div>
						
						<p id="formbuttons" class="prepend-1"><input type="submit" id="add_link" name="add_link" value="Add"></p>
					</div>
				</div>
			</div>
			
		</div>
		
			<div class="container">
				<p class="span-10 column first"><label for="name" class="incontent"><?php _e( 'Blog Name', 'blogroll' ); ?></label>
				<input type="text" id="name" name="name" size="100%" value="<?php echo isset($name)?$name:''; ?>"  class="styledformelement"></p>
				
				<p class="span-10 column last"><label for="url" class="incontent"><?php _e( 'Blog URL', 'blogroll' ); ?></label>
				<input type="text" id="url" name="url" size="100%" value="<?php echo isset($url)?$url:''; ?>" class="styledformelement"></p>
			</div>

			<p><label for="description" class="incontent"><?php _e( 'Description', 'blogroll' ); ?></label>
			<textarea id="description" name="description" rows="20" cols="114" class="styledformelement resizable"><?php echo isset($description)?$description:''; ?></textarea></p>
			
			<div class="container">
				<p class="span-10 column first"><label for="feed" class="incontent"><?php _e( 'Feed URL', 'blogroll' ); ?></label>
				<input type="text" id="feed" name="feed" size="100%" value="<?php echo isset($feed)?$feed:''; ?>" class="styledformelement"></p>
				
				<p class="span-10 column last"><label for="owner" class="incontent"><?php _e( 'Owner Name', 'blogroll' ); ?></label>
				<input type="text" id="owner" name="owner" size="100%" value="<?php echo isset($owner)?$owner:''; ?>" class="styledformelement"></p>
			</div>

			<div id="formbuttons" class="container">
				<p><?php if ( isset($id) ) : ?><input type="hidden" id="id" name="id" value="<?php echo $id; ?>"><?php endif; ?>
				<input type="submit" id="submit_options" name="submit_options" value="Save"></p>
			</div>
		</form>
	
	
</div>
</div>

<?php include HABARI_PATH . '/system/admin/footer.php'; ?>
