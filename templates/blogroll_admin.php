<?php include HABARI_PATH . '/system/admin/header.php'; ?>

<div class="container">
<hr>
	<?php if(Session::has_messages()) {Session::messages_out();} ?>
	
		<h1>Manage Blogroll</h1>
		<p>Below you can edit blogroll links.</p>
	
		<form name="form_blogroll" id="form_blogroll" action="<?php URL::out('admin', 'page=blogroll'); ?>" method="post">
		
		<div class="container pagesplitter">
			<ul class="tabcontrol tabs">
				<li class="first last"><a href="#quick-link-blogroll">Add Quick Link</a></li>
			</ul>
			<div id="quick-link-blogroll" class="splitter">
				<div class="splitterinside">
					<div class="container">
						<p class="prepend-2">Enter the feed url of the blog, other information will be "auto-discovered".</p>
						
						<div class="container publish">
							<p class="prepend-1"><label for="feed" class="incontent">Feed URL</label>
							<input type="text" id="feed" name="feed" size="100%" value="" class="styledformelement"></p>
						</div>
						
						<p id="formbuttons" class="prepend-1"><input type="hidden" id="auto_update" name="auto_update" value="1">
						<input type="submit" id="add_link" name="add_link" value="Add"></p>
					</div>
				</div>
			</div>
			
		</div>
		
	
	
	<table id="post-data-published" width="100%" cellspacing="0">
		<thead>
			<tr>
				<th align="right"> </th>
				<th align="left"><?php _e('Name'); ?></th>
				<th align="left"><?php _e('Feed URL'); ?></th>
				<th align="left"><?php _e('Owner Name'); ?></th>
				<th align="left"><?php _e('Description'); ?></th>
				<th align="center"> </th>
				<th align="center"> </th>
			</tr>
		</thead>
	
	<?php foreach ( Blogs::get() as $blog ) : ?>
	<tr>
			<td class="span-1"><input type="checkbox" name="blog_ids[]" value="<?php echo $blog->id; ?>"></td>
			<td class="span-5"><?php echo '<a href="' . $blog->url . '">' . Utils::truncate( $blog->name, 32, false ) . '</a>'; ?></td>
			<td class="span-3"><?php echo $blog->feed ?></td>
			<td class="span-3"><?php echo $blog->owner ?></td>
			<td class="span-5"><?php echo Utils::truncate( $blog->description, 128, false ); ?></td>
			<td class="span-3">
				<a class="edit" href="<?php URL::out('admin', 'page=blogroll&id=' . $blog->id); ?>" title="Edit this entry">
					Edit
				</a>
			</td>
			<td class="span-3 last">
				<a class="edit" href="<?php URL::out('admin', 'page=blogroll&id=' . $blog->id .'&auto_update=1'); ?>" title="Automatically update this blogs information.">
					Auto Update
				</a>
			</td>
		</tr>
		<?php endforeach; ?>
	
	<tr><td colspan="7">
	Selected blogs: &nbsp;&nbsp;<select name="change" class="longselect">
	<option value="delete"><?php _e('Delete'); ?></option>
	</select>
	<input type="submit" name="do_update" value="<?php _e('Update'); ?>">
	</td></tr>
	</table>
	</div>
	
	</form>
	
</div>

<?php include HABARI_PATH . '/system/admin/footer.php'; ?>
