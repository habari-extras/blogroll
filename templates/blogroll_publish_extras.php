	<div class="container">
		<p class="column span-5"><?php _e( 'Feed URL', 'blogroll' ); ?></p>
		<p class="column span-14 last"><input type="text" id="feed" name="feed" size="100%" value="<?php echo isset($feed)?$feed:''; ?>" class="styledformelement"></p>
	</div>
	
	<hr>
	
	<div class="container">
		<p class="column span-5"><?php _e( 'Owner Name', 'blogroll' ); ?></p>
		<p class="column span-14 last"><input type="text" id="owner" name="owner" size="100%" value="<?php echo isset($owner)?$owner:''; ?>" class="styledformelement"></p>
	</div>
	
	<hr>
	
	<div class="container">
		<p class="column span-5"><?php _e( 'Relationship', 'blogroll' ); ?></p>
		<p class="column span-14 last">
	 	<label><?php echo Utils::html_select( 'rel', $relationships, $rel, array( 'class'=>'longselect') ); ?></label>
		</p>
	</div>

