<?php include HABARI_PATH . '/system/admin/header.php'; ?>

	<div class="container">
		<h2><?php _e( 'Manage Blogroll', 'blogroll' ); ?></h2>
		<p>
		<?php _e( 'Below you can view and edit blogroll links. You can also import links from an OPML file.', 'blogroll' ); ?>
		<?php printf( _t( 'Or you can <a href="%s">Publish a new Blogroll link</a>.', 'blogroll' ), URL::get( 'admin', 'page=blogroll_publish' ) ); ?>
		</p>
	</div>
		
		<form name="form_blogroll" enctype="multipart/form-data" id="form_blogroll" action="<?php URL::out('admin', 'page=blogroll_manage'); ?>" method="post">
		
		<div class="pagesplitter">
			<ul class="tabcontrol tabs">
				<li class="import_opml_blogroll first" ><a href="#import_opml_blogroll" style="width: 125px;"><?php _e( 'Import/Export OPML', 'blogroll' ); ?></a></li><li class="bookmarklets_blogroll last"><a href="#bookmarklets_blogroll"><?php _e( 'Bookmarklets', 'blogroll' ); ?></a></li>
			</ul>
			
			<div id="import_opml_blogroll" class="splitter">
				<div class="splitterinside publish">
					<div class="container">
						<h2><?php _e( 'Import', 'blogroll' ); ?></h2>
						<p><?php _e( 'Upload or enter the URL of the OPML file to import.', 'blogroll' ); ?></p>
						
						<div>
							<p><label for="opml_file" class="incontent"><?php _e( 'URL', 'blogroll' ); ?></label>
							<input type="text" id="opml_file" name="opml_file" size="100%" value="" class="styledformelement"></p>
							
							<p><input type="file" id="userfile" name="userfile" size="50%" value="" class="styledformelement"></p>
						</div>
						
						<p><input type="submit" id="import_opml" name="import_opml" value="<?php _e( 'Import', 'blogroll' ); ?>"></p>
					</div>
					<hr>
					<div class="container">
						<h2><?php _e( 'Export', 'blogroll' ); ?></h2>
						
						<div class="">
							<ul class="prepend-1">
								<li><a href="<?php URL::out( 'blogroll_opml' ); ?>"><?php _e( 'Export as OPML 1.1', 'blogroll' ); ?></a></li>
								<li><p>Export as OPML 2.0 with ponies attached. OMG PONIES! (coming soon)</p></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			
			<div id="bookmarklets_blogroll" class="splitter">
				<div class="splitterinside publish">
					<div class="container">
						<p><?php _e( 'Add the following "Bookmarklets" to your browsers bookmarks, for easy Blogrolling of your favourite sites.', 'blogroll' ); ?></p>
						
						<div>
							<ul class="prepend-1">
								<li><a href="<?php printf( "javascript:location.href='%s?url='+encodeURIComponent(location.href)+'&name='+encodeURIComponent(document.title)", URL::get( 'admin', 'page=blogroll_publish' ) ); ?>"><?php _e ( 'Add to Blogroll', 'blogroll' ); ?></a></li>
								<li><a href="<?php printf( "javascript:location.href='%s?quick_link_bookmarklet='+encodeURIComponent(location.href)", URL::get( 'admin', 'page=blogroll_publish' ) ); ?>"><?php _e ( 'Quick Link Blogroll', 'blogroll' ); ?></a></li>
							</ul>
						</div>
					</div>
				</div>
			</div>
			
		</div>
		
	
	<div class="container">
	<div>
		
	</div>

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
	
	<tr><td colspan="6" class="first last">
		<a href="#" onclick="$('.blog_ids').attr('checked', 'checked');return false;"><?php _e('Check All', 'blogroll'); ?></a> |
		<a href="#" onclick="$('.blog_ids').attr('checked', '');return false;"><?php _e('Uncheck All', 'blogroll'); ?></a>
	</td></tr>
	
	<?php foreach ( Blogs::get() as $blog ) : ?>
	<tr>
			<td class="span-1 first"><input type="checkbox" class="blog_ids" name="blog_ids[]" value="<?php echo $blog->id; ?>"></td>
			<td class="span-4"><?php echo '<a href="' . $blog->url . '">' . Utils::truncate( $blog->name, 32, false ) . '</a>'; ?></td>
			<td class="span-1"><?php if ( $blog->feed ) : ?><a href="<?php echo $blog->feed; ?>"><img style="padding:0; margin:0 1em;" src="<?php echo $feed_icon; ?>"></a><?php endif; ?></td>
			<td class="span-4"><?php echo $blog->owner ?></td>
			<td class="span-10"><?php echo Utils::truncate( $blog->description, 128, false ); ?></td>
			<td class="span-2 last">
				<a class="link_as_button" href="<?php URL::out('admin', 'page=blogroll_publish&id=' . $blog->id); ?>" title="Edit this entry">
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
	

<?php include HABARI_PATH . '/system/admin/footer.php'; ?>
