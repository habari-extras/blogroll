<?php include HABARI_PATH . '/system/admin/header.php'; ?>

<div class="container"><?php if(Session::has_messages()) {Session::messages_out();} ?></div>
	
	<div class="container">
		<h2><?php _e( 'Manage Blogroll', 'blogroll' ); ?></h2>
		<p>
		<?php _e( 'Below you can view and edit blogroll links. You can also import links from an OPML file.', 'blogroll' ); ?>
		<?php printf( _t( 'Or you can <a href="%s">Publish a new Blogroll link</a>.', 'blogroll' ), URL::get( 'admin', 'page=blogroll_publish' ) ); ?>
		</p>
	</div>
	
	<hr>
	
		<form name="form_blogroll" enctype="multipart/form-data" id="form_blogroll" action="<?php URL::out('admin', 'page=blogroll_manage'); ?>" method="post">
		
		<div class="container pagesplitter">
			<ul class="tabcontrol tabs">
				<li class="import_opml_blogroll first"><a href="#import_opml_blogroll"><?php _e( 'Import OPML', 'blogroll' ); ?></a></li><li class="export_blogroll"><a href="#export_blogroll"><?php _e( 'Export OPML', 'blogroll' ); ?></a></li><li class="bookmarklets_blogroll last"><a href="#bookmarklets_blogroll"><?php _e( 'Bookmarklets', 'blogroll' ); ?></a></li>
			</ul>
			
			<div id="import_opml_blogroll" class="splitter">
				<div class="splitterinside publish">
					<div class="container">
						<p><?php _e( 'Upload or enter the URL of the OPML file to import.', 'blogroll' ); ?></p>
						
						<div class="container">
							<p><label for="opml_file" class="incontent"><?php _e( 'URL', 'blogroll' ); ?></label>
							<input type="text" id="opml_file" name="opml_file" size="100%" value="" class="styledformelement"></p>
							
							<p>OR</p>
							
							<p><input type="file" id="userfile" name="userfile" size="50%" value="" class="styledformelement"></p>
						</div>
						
						<p><input type="submit" id="import_opml" name="import_opml" value="<?php _e( 'Import', 'blogroll' ); ?>"></p>
					</div>
				</div>
			</div>
			
			<div id="export_blogroll" class="splitter">
				<div class="splitterinside publish">
					<div class="container">
						<p><?php _e( 'Export your blogroll in different formats.', 'blogroll' ); ?></p>
						
						<div class="container">
							<ul>
								<li>Export as standard OPML</li>
								<li>Export as special OPML with ponies attached. OMG PONIES!</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			
			<div id="bookmarklets_blogroll" class="splitter">
				<div class="splitterinside publish">
					<div class="container">
						<p><?php _e( 'Add the following "Bookmarklets" to your browsers bookmarks, for easy Blogrolling of your favourite sites.', 'blogroll' ); ?></p>
						
						<div class="container">
							<ul>
								<li><a href="<?php printf( "javascript:location.href='%s?url='+encodeURIComponent(location.href)+'&name='+encodeURIComponent(document.title)", URL::get( 'admin', 'page=blogroll_publish' ) ); ?>"><?php _e ( 'Add to Blogroll', 'blogroll' ); ?></a></li>
								<li><a href="<?php printf( "javascript:location.href='%s?quick_link_bookmarklet='+encodeURIComponent(location.href)", URL::get( 'admin', 'page=blogroll_publish' ) ); ?>"><?php _e ( 'Quick Link Blogroll', 'blogroll' ); ?></a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			
		</div>
		
	
	<div class="container">
	<table id="post-data-published" width="100%" cellspacing="0">
		<thead>
			<tr>
				<th align="right"> </th>
				<th align="left"><?php _e( 'Name', 'blogroll' ); ?></th>
				<th align="left"> </th>
				<th align="left"><?php _e( 'Owner', 'blogroll' ); ?></th>
				<th align="left"><?php _e( 'Description', 'blogroll' ); ?></th>
				<th align="center"> </th>
			</tr>
		</thead>
	
	<?php foreach ( Blogs::get() as $blog ) : ?>
	<tr>
			<td class="span-1 first"><input type="checkbox" name="blog_ids[]" value="<?php echo $blog->id; ?>"></td>
			<td class="span-4"><?php echo '<a href="' . $blog->url . '">' . Utils::truncate( $blog->name, 32, false ) . '</a>'; ?></td>
			<td class="span-1"><?php if ( $blog->feed ) : ?><a href="<?php echo $blog->feed; ?>"><img style="padding:0; margin:0 1em;" src="<?php echo $feed_icon; ?>"></a><?php endif; ?></td>
			<td class="span-4"><?php echo $blog->owner ?></td>
			<td class="span-10"><?php echo Utils::truncate( $blog->description, 128, false ); ?></td>
			<td class="span-2 last">
				<a class="edit" href="<?php URL::out('admin', 'page=blogroll_publish&id=' . $blog->id); ?>" title="Edit this entry">
					<?php _e( 'Edit', 'blogroll' ); ?>
				</a>
			</td>
		</tr>
		<?php endforeach; ?>
	
	<tr><td colspan="6" class="first last">
		<?php _e( 'Selected blogs: ', 'blogroll' ); ?>&nbsp;&nbsp;<select name="change" class="longselect">
		<option value="delete"><?php _e( 'Delete', 'blogroll' ); ?></option>
		<option value="auto_update"><?php _e( 'Auto Update', 'blogroll' ); ?></option>
		</select>
		<input type="submit" name="do_update" value="<?php _e( 'Update', 'blogroll' ); ?>">
	</td></tr>
	</table>
	</div>
	
	</form>
	
</div>

<?php include HABARI_PATH . '/system/admin/footer.php'; ?>
