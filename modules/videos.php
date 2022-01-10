<?php 
foreach ($id as $p) {
    $data_video = get_option('get_videos_'.$p);
    
    $video_type = isset($data_video['video_type'])?$data_video['video_type']:'upload_video';

    $video_poster_img_source = isset($data_video['video_poster_img_source'])?$data_video['video_poster_img_source']:'';
    $video_poster_img_id = isset($data_video['video_poster_img_id'])?$data_video['video_poster_img_id']:'';

    $video_url_mp4 = isset($data_video['video_url_mp4'])?$data_video['video_url_mp4']:'';
    $video_url_ogv = isset($data_video['video_url_ogv'])?$data_video['video_url_ogv']:'';
    $video_url_webm = isset($data_video['video_url_webm'])?$data_video['video_url_webm']:'';
    
    $video_url_vimeo = isset($data_video['video_url_vimeo'])?$data_video['video_url_vimeo']:'';
    $video_url_youtube = isset($data_video['video_url_youtube'])?$data_video['video_url_youtube']:'';

    $video_autoplay = isset($data_video['video_autoplay'])?$data_video['video_autoplay']:'no';
    $video_muted = isset($data_video['video_muted'])?$data_video['video_muted']:'no';
    
    $video_controls = isset($data_video['video_controls'])?$data_video['video_controls']:'no';
    $video_loop = isset($data_video['video_loop'])?$data_video['video_loop']:'no';
    $video_height = isset($data_video['video_height'])?$data_video['video_height']:'auto';
    $video_width = isset($data_video['video_width'])?$data_video['video_width']:'100%';

    if($video_width=="")
        $video_width = '100%';

    if($video_height=="")
        $video_height = 'auto';

    $video = '<span class="improveseo_row">';
    if($video_type=="upload_video"){
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
    }elseif($video_type=="youtube"){
        $youtube_url = improveseo_generate_youtube_url($video_url_youtube);
        $youtube_url .= '?rel=0';

        if($video_autoplay=="yes")
            $youtube_url .= '&autoplay=1';

        if($video_muted=="yes")
            $youtube_url .= '&mute=1';

        if($video_controls=="yes")
            $youtube_url .= '&controls=0';

        if($video_loop=="yes")
            $youtube_url .= '&loop=1';


        $video .= '<iframe src="'.$youtube_url.'" width="'.$video_width.'" height="'.$video_height.'"></iframe>';
    }elseif($video_type=="vimeo"){
        $vimeo_url = improveseo_generate_vimeo_url($video_url_vimeo);
        $vimeo_url .= '?';
        $allow = $video_style = '';

        if($video_autoplay=="yes"){
            $vimeo_url .= '&autoplay=1';
            $allow .= 'autoplay;';
        }
        $allow .= 'fullscreen; picture-in-picture';
        
        if($video_loop=="yes")
            $vimeo_url .= '&loop=1';
        
        if($video_muted=="yes")
            $vimeo_url .= '&mute=1';

        if($video_controls=="yes")
            $vimeo_url .= '&controls=0';
        
        $vimeo_url .= '&portrait=0';
        
        if($video_height=="auto" && $video_width=="100%"){
            $video .= '<div style="padding:56.25% 0 0 0;position:relative;">';
            $video_style .= 'position:absolute;top:0;left:0;width:100%;height:100%;';
        }
        $video .= '<iframe src="'.$vimeo_url.'" width="'.$video_width.'" height="'.$video_height.'" frameborder="0" allow="'.$allow.'" style="'.$video_style.'"></iframe>';
        if($video_height=="auto" && $video_width=="100%"){
            $video .= '</div><script src="https://player.vimeo.com/api/player.js"></script>';
        }
    }
    $video .= '</span>';
    echo $video;
}