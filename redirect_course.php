<?php
/**
 * Plugin Name: Course Redirects for Learndash
 * Description: Course Redirects for Learndash Plugin provide you ability to redirect on page which you want to show after completion of course.You can easily see Page,Post,Category and Tag Id's.You can see Visitors and Views of your website.
 * Version: 0.4
 * Author: Chandra Bhushan Singh
 *
 * 
 */
function crfl_save_table()
{
    global $table_prefix, $wpdb;

    $tblname = 'redirect_course';
    $wp_track_table = $table_prefix . "$tblname ";

    #Check to see if the table exists already, if not, then create it

    if($wpdb->get_var( "show tables like '$wp_track_table'" ) != $wp_track_table) 
    {

        $sql = "CREATE TABLE $wp_track_table (
          `page_id` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_bin;";
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        dbDelta($sql);
    }
}
 register_activation_hook( __FILE__, 'crfl_save_table' );

 function crfl_unsave_table()
{
    global $table_prefix, $wpdb;

    $tblname = 'redirect_course';
	$vvcname = 'visitor_views_counter';
	$table_name = $wpdb->prefix."$vvcname ";
	$wpdb->query("DROP table IF Exists $table_name");
    $wp_track_table = $table_prefix . "$tblname ";
        $sql = "DROP table IF Exists $wp_track_table ";
        $wpdb->query($sql);
    
}
 register_deactivation_hook( __FILE__, 'crfl_unsave_table' );
 
 
 add_action("learndash_course_completed", function($data) {
  global $table_prefix, $wpdb;

    $tblname = 'redirect_course';
    $wp_track_table = $table_prefix . "$tblname ";
  $sql = $wpdb->get_results("SELECT * FROM $wp_track_table");
  foreach($sql as $row){   
    $page_id = $row->page_id;
  }
	wp_redirect( get_permalink( $page_id ) );
	die();
  }, 
  20
);
add_action("admin_menu","crfl_addmenu");
function crfl_addmenu(){


$settings_page  = add_menu_page( 'Page Settings', 'Course Redirects for Learndash', 'administrator', 'page-settings', 'crfl_redirect_setting' );
    $page           = add_submenu_page( 'vvc-counter', 'Dashboard', 'Dashboard', 'administrator', 'vvc-counter', 'vvc_hits_counter_graphs' );
    $page           = add_submenu_page( 'page-settings', 'Visitor & Views Counter Dashboard', 'Visitor & Views Counter', 'administrator',  'visitor-views-counter','vvc_hits_counter_graphs');
}
function crfl_redirect_setting(){
  
  global $table_prefix, $wpdb;

    $tblname = 'redirect_course';
    $wp_track_table = $table_prefix . "$tblname ";
  
     if (isset($_POST['id'])){
		
     $page_id = (int)$_POST['id'];
     $sql ="INSERT INTO $wp_track_table (`page_id`) VALUES ('$page_id');";
     require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        dbDelta($sql);
		if($page_id != null){
			echo "<p>Saved Page ID $page_id.</p>" ;
		}
		else{
			echo"  ";
		}
		
  }
         
	echo "<h2>Page Settings</h2>";
    echo "<p>Enter correct page id which you want to show users after course completion.</p>";
	
	echo '<form method="post">
	<table align="left" border="0" cellspacing="0" cellpadding="3">
	<tr><td style="font: message-box;">Page Id:</td><td><input type="number" name="id" maxlength="30" required></td></tr>
  </br>
	<tr><td><input type="submit" value="Submit"></td></tr>
	
	</table>
        </form>';	
}
  //For display Page,Post,Category and Tag Id's//

function adding_id_number_to_admin_bar( $wp_admin_bar ) {

	 global $wp_query;

	$post_id_num = "";
	$post_type = get_post_type();

	
	if( $post_type === 'page' || $post_type === 'post' ) {		
		$post_id_num =  "ID: " . get_the_ID();
	} 

	
	if( is_category() || is_tag() ) {

		$id_type = "";
		if( is_category() ) { $id_type = "Category ";}
		if( is_tag() ) { $id_type = "Tag ";}

		$post_id_num =  $id_type . "ID: " . $wp_query->get_queried_object_id();
		
	} 

	
	if( is_home() ) {
		$blog_page_id = get_option( 'page_for_posts' );

		
		if( !empty($blog_page_id) ) {
			$post_id_num =  "ID: " . $blog_page_id;
		} 

	} 
	
	if( class_exists('woocommerce') ) {

		
		if( $post_type === 'product' ) {		
			$post_id_num =  "ID: " . get_the_ID();
		} 

		
		if( is_product_category() || is_product_tag() ) {

			$id_type = "";
			if( is_product_category() ) { $id_type = "Category ";}
			if( is_product_tag() ) { $id_type = "Tag ";}

			$post_id_num =  $id_type . "ID: " . $wp_query->get_queried_object_id();
			
		} 

	} 

	if( !empty($post_id_num) ) {

		$args = array(
			'id'    => 'my_page',
			'title' => $post_id_num,
			'href'  => '',
			'meta'  => array( 'class' => 'my-toolbar-page' )
		);

		$wp_admin_bar->add_node( $args );

	} 

} 

add_action( 'admin_bar_menu', 'adding_id_number_to_admin_bar', 9999 );

function add_id_title_to_table($columns) {
	return array_merge( $columns, array('show_id_num' => __('ID')) );
} 

add_filter('manage_posts_columns' , 'add_id_title_to_table', 1); 
add_filter('manage_pages_columns' , 'add_id_title_to_table', 1); 
add_filter('manage_media_columns' , 'add_id_title_to_table', 1); 
add_filter('manage_edit-comments_columns' , 'add_id_title_to_table', 1); 
add_filter('manage_edit-category_columns' , 'add_id_title_to_table', 1); 
add_filter('manage_edit-post_tag_columns' , 'add_id_title_to_table', 1); 

function add_id_number_to_id_column( $column, $id ) {
	if( $column === "show_id_num" ) {
		echo $id;
	} 
} 

add_action('manage_posts_custom_column' , 'add_id_number_to_id_column', 2, 2); 
add_action('manage_pages_custom_column' , 'add_id_number_to_id_column', 2, 2); 
add_action('manage_media_custom_column' , 'add_id_number_to_id_column', 2, 2); 
add_action('manage_comments_custom_column' , 'add_id_number_to_id_column', 2, 2); 



function add_id_number_to_categories_tags( $content, $column_name, $term_id ) {

	if( $column_name === "show_id_num" ) {
		echo $term_id;
	} 

} 

add_action('manage_category_custom_column' , 'add_id_number_to_categories_tags', 2, 3); 
add_action('manage_post_tag_custom_column' , 'add_id_number_to_categories_tags', 2, 3); 

add_action('admin_head', function() {

	$output_code = "";
    $output_code .= "<style>";
		$output_code .= "th#show_id_num {width: 55px;}";
    $output_code .= "</style>";

	echo $output_code;

}); 
//For display Page,Post,Category and Tag Id's//
//counter//
register_activation_hook(__FILE__, 'vvc_hits_counter_installNewTables');
function vvc_hits_counter_installNewTables() {
    global $wpdb;
    $tableName = $wpdb->prefix . "visitor_views_counter";

    $sqlCmd = "CREATE TABLE IF NOT EXISTS " . $tableName . "(
	vvc_id mediumint(9) UNSIGNED AUTO_INCREMENT NOT NULL,
	vvc_date date,
	vvc_time time,
	vvc_post_id mediumint(9),
	vvc_visitors_count mediumint(9),
	vvc_views_count mediumint(9),
	PRIMARY KEY (vvc_id)
	)DEFAULT CHARSET=utf8;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sqlCmd);
}
register_activation_hook(__FILE__, 'vvc_get_previous_visitors_views');
function vvc_get_previous_visitors_views(){
    if(get_option('migrated_to_version') != 1){
        $vvc_pageViews_count = get_option('vvc_pageViews_count');
        $vvc_visitors_count = get_option('vvc_visitors_count');
        vvc_reset_views_visitors(0, $vvc_visitors_count, $vvc_pageViews_count);
        update_option('migrated_to_version', '1' );
        update_option('vvc_update_ran' ,1);
    }
}

// MIGRATION
add_action('wp_head', 'vvc_plugin_data_migration');
add_action('admin_head', 'vvc_plugin_data_migration');
function vvc_plugin_data_migration(){
    if(get_option('vvc_simple_hits_counter_version')!='1.0.2'){
        vvc_hits_counter_installNewTables();
        vvc_get_previous_visitors_views();
        update_option('vvc_simple_hits_counter_version', '1.0.2');
        update_option('vvc_update_ran', intval(get_option('vvc_update_ran'))+1 );
    }
}


// ENQUEUE SCRIPTS
add_action('wp_footer','vvc_simple_hits_counter_js');
function vvc_simple_hits_counter_js() { ?>
    <script type="text/javascript">
        var templateUrl = '<?php echo get_site_url(); ?>';
        var post_id = '<?php echo get_the_ID(); ?>';
    </script>
    <?php  wp_enqueue_script( 'vvc_simple_hits_counter_js', plugins_url( '/js/vvc_simple_hits_counter_js.js', __FILE__ ), array('jquery'), '', true);
}

// UPDATE COUNTER
add_action('wp_ajax_vvc_update_counter','visitor_views_counter');
add_action('wp_ajax_nopriv_vvc_update_counter','visitor_views_counter');
function visitor_views_counter(){
    $post_id = sanitize_text_field($_GET['post_id']);
    $visitors = $views = 0;

    if(!isset($_COOKIE['vvc_unique_visitor'])){
        setcookie("vvc_unique_visitor", "1", 0 ,'/', parse_url(site_url(), PHP_URL_HOST));
        $visitors = 1;
    }

    $views = 1;
    vvc_update_views_visitors($post_id, $visitors, $views);
}
function vvc_update_views_visitors($post_id, $visitors, $views){
    global $wpdb;
    $table_name = $wpdb->prefix.'visitor_views_counter';
    $date = Date("Y-m-d");
    $time = Date("h:i:s");
    $post_data = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE (vvc_post_id = %d AND vvc_date = %s )", $post_id, $date));
    $visitors = $post_data[0]->vvc_visitors_count+$visitors;
    $views = $post_data[0]->vvc_views_count+$views;
    if($post_data){
        $wpdb->update($table_name, array('vvc_visitors_count' => $visitors, 'vvc_views_count' => $views), array('vvc_post_id' => $post_id, 'vvc_date' => "$date"));
    }else{
        $sql = "INSERT INTO $table_name (`vvc_id`, `vvc_date`, `vvc_time`, `vvc_post_id`, `vvc_visitors_count`, `vvc_views_count`) VALUES (NULL, '".$date."', '".$time."', $post_id, $visitors, $views)";
        $wpdb->insert(
            $table_name,
            array(
                'vvc_id' => NULL,
                'vvc_date' => $date,
                'vvc_time' => $time,
                'vvc_post_id' => $post_id,
                'vvc_visitors_count' =>$visitors,
                'vvc_views_count' => $views
            )
        );
    }
}
function vvc_reset_views_visitors($post_id, $visitors, $views){
    global $wpdb;
    $table_name = $wpdb->prefix.'visitor_views_counter';
    $date = Date("Y-m-d");
    $time = Date("h:i:s");
    $post_data = $wpdb->get_results("SELECT * FROM $table_name WHERE vvc_post_id = $post_id");
    if($post_data){
        if($visitors == 'null'){
            $visitors = $post_data[0]->vvc_visitors_count;
        }
        if($views == 'null'){
            $views = $post_data[0]->vvc_views_count;
        }
        $wpdb->update($table_name, array('vvc_visitors_count' => $visitors, 'vvc_views_count' => $views), array('vvc_post_id' => $post_id));
    }else{
        if($visitors == 'null'){
            $visitors = 0;
        }
        if($views == 'null'){
            $views == 0;
        }
        $wpdb->insert(
            $table_name,
            array(
                'vvc_id' => NULL,
                'vvc_date' => $date,
                'vvc_time' => $time,
                'vvc_post_id' => $post_id,
                'vvc_visitors_count' =>$visitors,
                'vvc_views_count' => $views
            )
        );
    }
}

function vvc_hits_counter_graphs(){
    global $wpdb;
    //load libraries for graph
    wp_enqueue_script( 'vvc_hits_counter_Chart_bundle_js', plugins_url( '/js/Chart.bundle.min.js', __FILE__ ), array('jquery'), '', true);
    wp_enqueue_script( 'vvc_hits_counter_Chart_js', plugins_url( '/js/Chart.min.js', __FILE__ ), array('jquery'), '', true);
    //check if user select last month or last week option   .. default to last week
    $dates_range = array();
    $begin = new DateTime();
    if(isset($_GET['range_filter']) && sanitize_text_field($_GET['range_filter']) == 'month'){

        $begin->sub(new DateInterval('P29D'));
        $end = new DateTime();
        $end->add(new DateInterval('P1D'));
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);
        foreach ( $period as $dt ){
            $dates_range[] = $dt->format( "Y-m-d" );
        }
    }else{
        $begin->sub(new DateInterval('P6D'));
        $end = new DateTime();
        $end->add(new DateInterval('P1D'));
        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($begin, $interval, $end);
        foreach ( $period as $dt ){
            $dates_range[] = $dt->format( "Y-m-d" );
        }
    }
    $table_name = $wpdb->prefix . 'visitor_views_counter';

    //retrieve data from database
    $post_data = $wpdb->get_results("SELECT vvc_id, vvc_date, vvc_time, vvc_post_id,  sum(vvc_visitors_count) vvc_visitors_count, SUM(vvc_views_count) vvc_views_count FROM $table_name WHERE vvc_date >= '".$begin->format( "Y-m-d" )."' GROUP BY vvc_date ORDER BY vvc_date DESC ");
    ?>
	
    <!--- generating html for filters -->
	
	<div class="filter" style="margin-top: 30px;
    width: 100%;
    TEXT-ALIGN: center;
    font-size: larger;font-weight: 700;">VISITOR & VIEWS DASHBOARD
	</div>
	
    <div class="filter" style="margin-top: 20px; width: 50%;margin-bottom: 2%;">
        <form action="" method="get" class="">
            <select name="range_filter" style="width: 20%">
                <option value="week"> Last Week</option>
                <option value="month" <?php if(isset($_GET['range_filter']) && sanitize_text_field($_GET['range_filter']) == 'month'){ echo "selected"; } ?>>Last Month</option>
            </select>
            <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']) ?>">
            <input type="submit" value="Apply" style="width: 10%" class="button button-primary">
        </form>

    </div>
	
    <!-- Add canvas in which we will show graph   -->
    <canvas id="vvc_visitors_views_charts" style="width: 75% !important;background:white !important"></canvas>
    <script>
        //javascript code for graph
        var visitors = [];
        var views = [];
        var date_labels = [];
        var counter = 0;
        <?php
        $vvc_visitors_count = 0;
        $vvc_views_count = 0;
        $vvc_date = 0;
        //$dates_range = array_reverse($dates_range);
        foreach ($dates_range as $date){
        $vvc_date = date("F j, Y", strtotime($date));
        foreach($post_data as $post){
            if(date("Y-m-d", strtotime($post->vvc_date)) == $date){
                $vvc_visitors_count = $post->vvc_visitors_count;
                $vvc_views_count = $post->vvc_views_count;
            }
        }
        ?>
        visitors[counter] = '<?php echo $vvc_visitors_count; ?>';
        views[counter]  = '<?php echo $vvc_views_count; ?>';
        date_labels[counter] = '<?php echo $vvc_date; ?>';
        counter ++;
        <?php
        $vvc_visitors_count = 0;
        $vvc_views_count = 0;
        }
        ?>
        window.chartColors = {
            red: 'rgb(255, 99, 132)',
            orange: 'rgb(255, 159, 64)',
            yellow: 'rgb(255, 205, 86)',
            green: 'rgb(75, 192, 192)',
            blue: 'rgb(54, 162, 235)',
            purple: 'rgb(153, 102, 255)',
            grey: 'rgb(201, 203, 207)'
        };
        var config = {
            type: 'line',
            data: {
                labels: date_labels,
                datasets: [{
                    label: "Visitors",
                    backgroundColor: window.chartColors.orange,
                    borderColor: window.chartColors.orange,
                    data: visitors,
                    fill: false,
                }, {
                    label: "Views",
                    fill: false,
                    backgroundColor: window.chartColors.purple,
                    borderColor: window.chartColors.purple,
                    data: views,
                }]
            },
            options: {
                responsive: true,
                title:{
                    display:true,
                    text:'LINE GRAPH PRESENTATION'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Month'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Value'
                        }
                    }]
                }
            }
        };
        window.onload = function() {
            var ctx = document.getElementById("vvc_visitors_views_charts").getContext("2d");
            window.myLine = new Chart(ctx, config);
        };
    </script>
    <?php
}


//counter//
 ?>