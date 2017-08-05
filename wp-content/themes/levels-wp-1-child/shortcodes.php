<?php
  /* Shortcodes for the Tactics Dashboard
   * Dependant on the WPcomplete plugin, which has to be activated.
   */

   // [course-contain] shortcode
   function shortcode_course_contain( $atts, $content = null ) {

     // create course container
     return
        '<div class="course-contain">' . do_shortcode($content) . '</div>';
   }
   add_shortcode('course-contain', 'shortcode_course_contain');

   // [course] shortcode
   function shortcode_course( $atts, $content = null ) {

     // create course
     return
       '<div class="course">' . do_shortcode($content) . '</div>';
   }
   add_shortcode('course', 'shortcode_course');
 ?>
