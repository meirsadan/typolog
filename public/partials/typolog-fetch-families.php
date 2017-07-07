<?php
	
header('Content-type: text/javascript');

echo json_encode( get_families_tree() );

exit();