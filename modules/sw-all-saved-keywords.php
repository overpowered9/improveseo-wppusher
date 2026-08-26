<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

    
    $saved_rand_nos_keywords = get_option('swsaved_random_nosofkeywords');
        if(empty($saved_rand_nos_keywords)) {
            return;
        }

?>
    <section class="project-table-wrapper pt-4">
        <div class="form table-responsive-sm">
     <table class="table widefat fixed wp-list-table widefat fixed table-view-list posts text-center">
        <thead>
    <tr>
        <th scope="col" style="width:20%" class="text-center manage-column manage-column column-title column-primary">No#</th>
        <th scope="col" style="width:60%"class="text-center manage-column">Project Name</th>
        <th scope="col" style="width:20%" class="text-center manage-column">Actions</th>
    </tr>
    </thead>
    <tbody>
        <?php 


            $html = '';
            $no = 1;
            foreach ($saved_rand_nos_keywords as $keyowrd_id) {

                $get_keyworddata = get_option('swsaved_keywords_with_results_'.$keyowrd_id);
                
                if (empty($get_keyworddata)) {
                    continue;
                }
                $html .= '<tr>';
                $html .= '<td class="column-title column-primary has-row-actions">'. esc_html( $no ) .' <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td>';

                $kw_proj_name = isset($get_keyworddata['proj_name']) ? $get_keyworddata['proj_name'] : '';
                

                $html .= '<td data-colname="Project Name">'. esc_html( $kw_proj_name ) .'</td>
                        <td class="actions-btn" data-colname="Actions"><span data-keyword_rand_id='.$keyowrd_id.' class="kw-download-kwproject wt-icons ct-btn btn btn-outline-primary mr-2">Save</span><span data-keyword_rand_id='.$keyowrd_id.' style="color:red" class="kw-dlt-kwproject wt-icons del-btn btn btn-outline-danger">Remove</span></td>
                        </tr>';

            
                $no++;
            }
            echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup assembled above with each value escaped at the point it is inserted; escaping the whole string would render the tags as text.
         ?>
         </tbody>
</table>
</div>
</section>