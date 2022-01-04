<?php 
foreach ($id as $p) {
    $data_video = get_option('get_videos_'.$p);
      
    $video_poster_img_source = isset($data_video['video_poster_img_source'])?$data_video['video_poster_img_source']:'';
    $video_poster_img_id = isset($data_video['video_poster_img_id'])?$data_video['video_poster_img_id']:'';

    $video_url_mp4 = isset($data_video['video_url_mp4'])?$data_video['video_url_mp4']:'';
    $video_url_ogv = isset($data_video['video_url_ogv'])?$data_video['video_url_ogv']:'';
    $video_url_webm = isset($data_video['video_url_webm'])?$data_video['video_url_webm']:'';

    $video_autoplay = isset($data_video['video_autoplay'])?$data_video['video_autoplay']:'no';
    $video_muted = isset($data_video['video_muted'])?$data_video['video_muted']:'no';
    
    $video_controls = isset($data_video['video_controls'])?$data_video['video_controls']:'no';
    $video_loop = isset($data_video['video_loop'])?$data_video['video_loop']:'no';
    $video_height = isset($data_video['video_height'])?$data_video['video_height']:'auto';
    $video_width = isset($data_video['video_width'])?$data_video['video_width']:'100%';

    if($video_width=="")
        $video_width = '100%';

    $video = '<div class="improveseo_row">';
    if($video_url_mp4!="" || $video_url_ogv!="" || $video_url_webm!=""){
        $video .= '<video width="'.$video_width.'" height="'.$video_height.'"';
        $video .= ' poster="'.$video_poster_img_source.'"';

        if($video_autoplay=="yes")
            $video .= ' autoplay';

        if($video_muted=="yes")
            $video .= ' muted';

        if($video_controls=="yes")
            $video .= ' controls';

        if($video_loop=="yes")
            $video .= ' loop';

        $video .= '>';

        if($video_url_mp4!="")
            $video .= '<source src="'.$video_url_mp4.'" type="video/mp4">';
        
        if($video_url_ogv!="")
            $video .= '<source src="'.$video_url_ogv.'" type="video/ogg">';
        
        if($video_url_webm!="")
            $video .= '<source src="'.$video_url_webm.'" type="video/webm">';

        $video .= 'Your browser does not support the video tag.';
        $video .= '</video>';
    }
    $video .= '</div>';
    echo $video;
}