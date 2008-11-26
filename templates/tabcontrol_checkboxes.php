<div class="container<?php echo ($class) ? ' ' . $class : ''?>"<?php echo ($id) ? ' id="' . $id . '"' : ''?>>
	<div class="pct25"><label><?php echo $this->caption; ?></label></div>
	<div class="pct75">
		<ul>
<?php foreach($options as $key => $text) : ?>
			<li><label><input type="checkbox" name="<?php echo $field; ?>[]" value="<?php echo $key; ?>" class="styledformelement"<?php echo ( in_array( $key, (array) $value ) ? ' checked' : '' ); ?>><?php echo htmlspecialchars($text); ?></label></li>
<?php endforeach; ?>
		</ul>
		<input type="hidden" name="<?php echo $field; ?>_submitted" value="1">
<?php if($message != '') : ?>
		<p class="error"><?php echo $message; ?></p>
<?php endif; ?>
	</div>
</div>
