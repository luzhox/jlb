<?php
    function excerpt($num) {
        $limit = $num+1;
        $excerpt = explode(' ', get_the_title(), $limit);
        array_pop($excerpt);
        $excerpt = implode(" ",$excerpt)."...";
        echo $excerpt;
    }

    function content($num) {
        $theContent = get_the_content();
        $output = preg_replace('/<img[^>]+./','', $theContent);
        $limit = $num+1;
        $content = explode(' ', $output, $limit);
        array_pop($content);
        $content = implode(" ",$content)."...";
        echo $content;
    }
?>