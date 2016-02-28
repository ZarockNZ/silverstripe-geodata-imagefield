<input type="hidden" name="MAX_FILE_SIZE" value="$MaxFileSize" />
<input $AttributesHTML />
<div class="googlemapfield $extraClass" $AttributesHTML>
	<div class="googlemapfield-controls">
		<% loop ChildFields %>
		$Field
		<% end_loop %>
	</div>
	<div class="googlemapfield-map"></div>
</div>
