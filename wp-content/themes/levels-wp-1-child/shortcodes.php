<?php
  /* Shortcodes for the Tactics Dashboard
   * Dependant on the WPcomplete plugin, which has to be activated.
   */
   // [course] shortcode
   function shortcode_course( $atts, $content = null ) {

     // create course
     return
       '<div class="course-contain"><div class="course">' . do_shortcode($content) . '</div></div>';
   }
   add_shortcode('course', 'shortcode_course');
 ?>
