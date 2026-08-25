<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
 

class improveseo_import_export{

    public function export($data, $filename = 'improve-seo'){
        @set_time_limit(0);

		$header_row = [];
		$data_row = [];

		foreach ($data[0] as $key => $value) {
			$header_row[] = $key;
		}

		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename=' . basename($filename.".csv"));
		header("Pragma: no-cache");
		header("Expires: 0");

		$fh = @fopen('php://output', 'w'); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- PHP output stream for a file download, not a filesystem path; WP_Filesystem has no equivalent

		fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF));

		fputcsv($fh, $header_row);

		foreach ($data as $key => $value) {
			$data_row = array_values((array) $value);
			fputcsv($fh, $data_row);
		}

		fclose($fh); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- PHP output stream for a file download, not a filesystem path; WP_Filesystem has no equivalent
    }


	public function import(){

		// write custom import funcitonlity here. 
		
	}

}

?>