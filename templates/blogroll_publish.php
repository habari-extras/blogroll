<?php include HABARI_PATH . '/system/admin/header.php'; ?>

	
		<form name="blogroll_quick_link" id="blogroll_quick_link" action="<?php URL::out('admin', 'page=blogroll_publish'); ?>" method="post">
		<div class="pagesplitter">
			<ul class="tabcontrol tabs">
				<li class="quick_link_blogroll first last"><a href="#quick_link_blogroll"><?php _e( 'Add Quick Link', 'blogroll' ); ?></a></li>
			</ul>
			
			<div id="quick_link_blogroll" class="splitter">
				<div class="splitterinside publish">
					<div class="container">
						<p><?php _e( 'Enter the URL or feed URL, other information will be "auto-discovered".', 'blogroll' ); ?></p>
						
						<div>
							<p><label for="quick_link" class="incontent"><?php _e( 'URL', 'blogroll' ); ?></label>
							<input type="text" id="quick_link" name="quick_link" size="100%" value="" class="styledformelement"></p>
						</div>
						
						<p><input type="submit" id="add_link" name="add_link" value="<?php _e( 'Add', 'blogroll' ); ?>"></p>
					</div>
				</div>
			</div>
		</div>
		</form>

		
		
		
		
		<form name="blogroll_publish" id="blogroll_publish" action="<?php URL::out('admin', 'page=blogroll_publish'); ?>" method="post">
		
		<div class="publish">
		<div class="container">
			<p><label for="name" class="incontent"><?php _e( 'Blog Name', 'blogroll' ); ?></label>
			<input type="text" id="name" name="name" size="100%" value="<?php echo isset($name)?$name:''; ?>"  class="styledformelement"></p>
			
			<p><label for="url" class="incontent"><?php _e( 'Blog URL', 'blogroll' ); ?></label>
			<input type="text" id="url" name="url" size="100%" value="<?php echo isset($url)?$url:''; ?>" class="styledformelement"></p>

			<p><label for="description" class="incontent"><?php _e( 'Description', 'blogroll' ); ?></label>
			<textarea id="description" name="description" rows="10" cols="114" class="styledformelement resizable"><?php echo isset($description)?$description:''; ?></textarea></p>
			
			<p><label for="tags" class="incontent"><?php _e( 'Tags, separated by, commas', 'blogroll' ); ?></label><input type="text" name="tags" id="tags" class="styledformelement" value="<?php if ( !empty( $tags ) ) { echo $tags; } ?>"></p>
		</div>
		</div>
			
			<div class="pagesplitter">
				<ul class="tabcontrol tabs">
					<?php
					$first = 'first';
					$ct = 0;
					$last = '';
					foreach($controls as $controlsetname => $controlset) :
						$ct++;
						if($ct == count($controls)) {
							$last = 'last';
						}
						$class = "{$first} {$last}";
						$first = '';
						$cname = preg_replace('%[^a-z]%', '', strtolower($controlsetname)) . '_settings';
						echo <<< EO_CONTROLS
<li class="{$cname} {$class}"><a href="#{$cname}">{$controlsetname}</a></li>
EO_CONTROLS;
					endforeach;
					?>
				</ul>

				<?php
				foreach($controls as $controlsetname => $controlset):
					$cname = preg_replace('%[^a-z]%', '', strtolower($controlsetname)) . '_settings';
				?>
				<div id="<?php echo $cname; ?>" class="splitter">
					<div class="splitterinside">
					<?php echo $controlset; ?>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			
			<div class="publish">
			<div id="formbuttons" class="container">
				<p class="column span-13" id="left_control_set">
					<input type="submit" id="save" name="save" class="publish" value="<?php _e( 'Save', 'blogroll' ); ?>">
				</p>
				
				<p class="column span-3 last" id="right_control_set"></p>
			</div>
			</div>
			<div id="hidden">
				<?php if ( !empty($id) ) : ?><input type="hidden" id="id" name="id" value="<?php echo $id; ?>"><?php endif; ?>
			</div>
</form>

<script type="text/javascript">
$(document).ready(function(){
	<?php if( !empty( $id ) ) : ?>
	$('#left_control_set #save').attr('value', '<?php _e( 'Update', 'blogroll' ); ?>');
	$('#left_control_set').append($('<input type="submit" name="auto_update" id="auto_update" value="<?php _e( 'Auto Update', 'blogroll' ); ?>">'));
	$('#auto_update').click(function(){
		$('#blogroll_publish')
			.append($('<input type="hidden" name="quick_link" value="<?php echo $url; ?>">'))
	});
	$('#right_control_set').append($('<input type="submit" name="delete" id="delete" class="delete" value="<?php _e( 'Delete', 'blogroll' ); ?>">'));
	$('#delete').click(function(){
		$('#blogroll_publish')
			.append($('<input type="hidden" name="change" value="delete"><input type="hidden" name="blog_ids[]" value="<?php echo $id; ?>">'))
			.attr('action', '<?php URL::out( 'admin', 'page=blogroll_manage' ); ?>');
	});
	<?php endif; ?>
});
</script>

	
<?php include HABARI_PATH . '/system/admin/footer.php'; ?>
