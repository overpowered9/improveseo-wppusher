<?php 

    
    if (empty($id)) {
        return;
    }


 ?>


<style type="text/css">

.container {

    max-width: 950px
}

.card {
    border-radius: 1rem;
    /*box-shadow: 0px -10px 0px rgb(151, 248, 6)*/
}

@media(max-width:767px) {
    .card {
        margin: 1rem 0.7rem 1rem;
        max-width: 80vw
    }
}

.img-testi {
    
    margin: 1.3rem auto 1rem auto;
    width: 30% !important;
    height: 70px !important;
    border-radius: 48% !important;
}

.col-md-4 {
    padding: 0 0.5rem
}

.card-title {
    font-size: 18px;
    margin-bottom: 0;
    font-weight: bold;
    font-family: 'IM Fell French Canon SC'
}

.card-text {
    font-style: oblique;
    text-align: center;
    padding: 1rem 2rem;
    font-size: 17px;
    color: rgb(82, 81, 81);
    line-height: 1.4rem
}

.footer {
    /*color: #36454F;*/
    color:grey;
    border-top: none;
    text-align: center;
    line-height: 1.2rem;
    padding: 2rem 0 1.4rem 0;
    font-family: 'Varela Round'
}

#name {
    font-size: 1.0rem;
    font-weight: bold
}

#position {
    font-size: 0.7rem
}

a {
    color: rgb(151, 248, 6);
    font-weight: bold
}

a:hover {
    color: rgb(151, 248, 6)
}
    
</style>
<div class="container">
    <div class="row">

            <?php 

                $html = '';
                foreach ($id as $i) {
                    $data = get_option('get_testimonials_'.$i);
                    
                    $tw_box_color = isset($data['tw_box_color']) ? $data['tw_box_color'] : '';
                    $tw_font_color = isset($data['tw_font_color']) ? $data['tw_font_color'] : '';
                    
                    $box_clr = 'color:white';
                    $font_clr = 'color:black';
                    if (!empty($tw_box_color)) {
                        $box_clr = 'background-color:'.$tw_box_color;
                    }

                    if (!empty($tw_font_color)) {
                        $font_clr = 'color:'.$tw_font_color;
                    }
                    
                    
                    $html .='<div class="col-md-4">';
                    $html .= '<div style='.$box_clr.' class="card d-flex mx-auto ">
                                <div style="text-align:center;" class="card-image"> <img class="img-testi img-fluid d-flex mx-auto" src='.$data['testi_img_src'].'> </div>
                                <div style='.$font_clr.' class="card-text">
                                    <div class="card-title"></div> '.$data['tw_testi_content'].'
                                </div>
                                <div class="footer"> <span id="name">'.$data['tw_testi_name'].'<br></span> <span id="position">'.$data['tw_testi_position'].'</span> </div>
                                </div>
                        </div>';
                }
                echo $html;
             ?>
    </div>
</div>