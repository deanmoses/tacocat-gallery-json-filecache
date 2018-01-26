<?php
	//
	// Script to retrieve a tacocat zenphoto album's JSON
	// and cache it on disk.
	//

	//
	// Figure out which album to update
	//

	if (!isset($_REQUEST['album'])) {
		http_response_code(400);
		echo(json_encode(['status' => 'error', 'message' => 'missing query string parameter \'album\'']));
		exit();
	}

	$album = htmlspecialchars($_REQUEST['album']);

	// Name of album must be length of either:
	//   0 (root album)
	//   4 (a year album), and consist of digits 
	if (strlen($album) != 0) {
		if (strlen($album) != 4) {
			http_response_code(400);
			echo(json_encode(['status' => 'error', 'message' => 'album must be 0 or 4 characters long']));
			exit();
		}
		else if (!ctype_digit($album)) {
			http_response_code(400);
			echo(json_encode(['status' => 'error', 'message' => 'album must be numeric']));
			exit();
		}
	}

	//
	// Retrieve JSON from zenphoto
	//

	try {
		// URL of which to retrieve contents
		$url = 'http://tacocat.com/zenphoto/' . $album . '?api';

		// retrieve contents of URL as a string
		$json_contents = file_get_contents($url);

		// apparently file_get_contents() returns FALSE for some errors
		if ($json_contents === false) {
		    http_response_code(500);
			echo(json_encode(['status' => 'error', 'message' => "Unspecified error attempting to retrieve album: $album"]));
			exit();
		}

		//
		// Write JSON to disk
		//

		// If this is the root album, which is blank (""),
		// save it to disk as root.json.
		if (!$album) $album = "root";

		// Path to JSON file on disk
		$file = getcwd() . '/' . $album . '.json';

		// Write the JSON to the file
		file_put_contents($file, $json_contents);

		// return JSON as response
		//echo($json_contents);

		//
		// Return success message in JSON format
		//
		echo(json_encode(['status' => 'success', 'message' => 'album ' . $album . ' has been refreshed']));

	} catch (Exception $e) {
		http_response_code(500);
		echo(json_encode(['status' => 'error', "Error attempting to retrieve album: $album: $e"]));
		exit();
	}
?>