<?php
/*
Plugin Name: Software Shop
Plugin URI: http://www.webdev3000.com
Description: Plugin implements self-maintaining Software Shop. Please don't forget to set your RegNow affiliate ID under <a href='/wp-admin/options-general.php?page=Software Shop'>Settings/Software Shop Settings</a>
Author: Csaba Kissi.
Version: 0.9.2
Author URI: http://www.webdev3000.com
*/  
//error_reporting(E_ALL);
//ini_set('display_errors','On');
global $post,$page;
 
require("software-shop-model.php");
$program = null;
$programs = null;
$categories = null;
$rns_shop = null;     
function regnow_admin_actions() {  
  add_options_page("Software Shop", "Software Shop", 1, "Software Shop", "sshop_options");  
}  

function rns_add_css() {
    echo '<link rel="stylesheet" href="'.get_bloginfo('wpurl').'/wp-content/plugins/software-shop/software-shop.css" type="text/css" media="screen" />';
}

function plugin_activate() {
  $my_post = array();
  $my_post['post_title'] = 'Softshop';
  $my_post['post_content'] = '[regnowshop]';
  $my_post['post_status'] = 'publish';
  $my_post['post_type'] = 'page';
  $my_post['post_author'] = 1;
  //$my_post['page_template'] = 'rnshop.php';
  $my_post['post_category'] = array(0);
  $id = wp_insert_post( $my_post );
  update_option('rgn_page_id', $id);
  update_option('rgn_aff_id', '19393');
}

function plugin_deactivate() {
    wp_delete_post(get_option('rgn_page_id'));
    delete_option('rgn_aff_id');
    delete_option('rgn_page_id');
}
   
function sshop_options() {
    if($_POST['rgn_hidden'] == 'Y') {
        $rgn_aff_id = $_POST['rgn_aff_id'];
        update_option('rgn_aff_id', $rgn_aff_id);
        ?> <br/><div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div> <?php
    }
    else {
        $rgn_aff_id = get_option('rgn_aff_id');
    }
    ?>
      <div class="wrap">
            <?php    echo "<h2>" . __( 'Software Shop Settings') . "</h2>"; ?>
            
            <form name="rgn_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
                <input type="hidden" name="rgn_hidden" value="Y">
                <?php    echo "<h4>" . __( 'Software Shop Options') . "</h4>"; ?>
                <p><?php _e("Your RegNow Affiliate ID: " ); ?><input type="text" name="rgn_aff_id" value="<?php echo $rgn_aff_id; ?>" size="7"><?php _e(" ex: 19393" ); ?></p>
                <p class="submit">
                <input type="submit" name="Submit" value="<?php _e('Update Options') ?>" />
                </p>
            </form>
            <hr />  
            <?php    
                $passed = 0;
                echo "<h4>" . __( 'Software Shop Connection Test') . "</h4>"; 
                echo "<p>allow_url_open test: ";
                if(ini_get('allow_url_fopen') == 1) { echo "<span style='background-color:#00FF00; padding:2px; border:solid 1px'>Passed</span>"; $passed = 1;}
                                                else echo "<span style='background-color:#FF0000; padding:2px; border:solid 1px'>Failed</span>";
                echo "</p>";
                echo "<p>CURL test: ";
                if (function_exists('curl_init')) { echo "<span style='background-color:#00FF00; padding:2px; border:solid 1px'>Passed</span>"; $passed = 1;}
                                              else echo "<span style='background-color:#FF0000; padding:2px; border:solid 1px'>Failed</span>";
                echo "</p>";
                echo "<p>fsockopen test: ";
                $fp = @fsockopen('www.regnow.com', 80, $errno, $errstr, 5); 
                if ($fp)    {  echo "<span style='background-color:#00FF00; padding:2px; border:solid 1px'>Passed</span>"; $passed = 1; }
                          else echo "<span style='background-color:#FF0000; padding:2px; border:solid 1px'>Failed</span>";
                echo "</p>";
                if($passed)    echo "<p>Connection Test passed. You should be able to use this plugin</p>"; 
                         else  echo "<p style='background-color:#FF8080;'>Regnow Test FAILED !!! You probably will be not able to use this plugin</p>"; 

                
            ?>  
        </div>
    <?
}

/* START view functions */
function displayShop() {
    global $rns_prop; 
    switch (get_query_var('op')) {
        case 'all': rns_showAll();
                    break;
        case 'cat': rns_showPrograms(get_query_var('id'),get_query_var('pi'));
                    break;
        case 'prog': rns_showProgram(get_query_var('id'));
       // echo "<pre>--"; print_r($rns_prop); echo "--*</pre>"; 
                    break;
        default: rns_showAll();            
      }           
}  
  /*** shows all categories ***/
  function rns_showAll() {
      global $categories;
      echo "<table cellpadding='10' width=90% class='rns_table'><tr>";
      foreach($categories as $item)  {
         if($cnt == 0) echo "<td valign='top'><table border=0>";
         if($item['subcategory'] == '')  echo "<tr><td><br /><b>".htmlspecialchars($item['category'])."</b></td></tr>\n";
                                    else echo "<tr><td><a href='".get_bloginfo('wpurl')."/softshop/category/".$item['ID']."'>".htmlspecialchars($item['subcategory'])."</a></td></tr>\n";    
         $cnt++;                           
         if($cnt == 50) { 
             echo "</table></td>";
             $cnt = 0;
         }                               
      }
      echo "</table></td>";
      echo "</tr></table>";
  }
  /*** shows programs in specific category ***/
  function rns_showPrograms() {
      global $programs;
      $programs[0]['CategoryName'] = str_replace("Software::","<a href='".get_bloginfo('wpurl')."/softshop/'>Software</a>::",$programs[0]['CategoryName']);
      echo "<strong>".str_replace("::"," : ",$programs[0]['CategoryName'])."</strong><hr/>";
      foreach($programs as $item)  {
         if($item['ShortDesc'] == '') $item['ShortDesc'] = substr($item['LongDesc'],0,70)."...";
         echo "<div class='box_1'><a href='".get_bloginfo('wpurl')."/softshop/".$item['ProductID']."''>".$item['ProductName']."</a><br/>".$item['ShortDesc']."</div>";
         echo "<div style='float:right'><a href='".$item['DirectPurchaseURL']."' class='buttons'>Buy Now...</a></div><div class='clear'></div>";
         echo "<div style='border-bottom: 1px solid #eee; margin-bottom:5px;'></div>";
      }    
      $pages_count = getPagesCount(getProductsCount($programs[0]['xml']));
      $page_current = get_query_var('pi');//get_query_var('pi');
      if($page_current < 1) $page_current++;
      $page_links = paginate_links( array(
         'base' => get_bloginfo('wpurl').'/softshop/category/'.get_query_var('id').'/%_%', /*'add_query_arg( 'paged', '%#%' ),*/
         'format' => '%#%',
         'prev_text' => __('&laquo;'),
         'next_text' => __('&raquo;'),
         'total' => $pages_count,
         'current' => $page_current
      ));
      echo '<div style="float:right;height:20px;">'.$page_links.'</div><div class="clear"></div>';
  }   
  
  /*** shows specific program details ***/
  function rns_showProgram() {
      global $program;
      $arr = explode("::",$program['CategoryName']);
      $category = $arr[count($arr)-1];
      echo "<a href='".get_bloginfo('wpurl')."/softshop/'>Software</a> : ".$arr[count($arr)-2]." : <a href='".get_bloginfo('wpurl')."/softshop/category/".$program['CategoryID']."'>".htmlentities($category)."</a>";
      echo "<h2>".htmlentities($program['ProductName'])."</h2>";
      if($program['Boxshot'] != '') echo "<div style='float:left; padding:10px'><img src='".$program['Boxshot']."' width='100' alt='".htmlentities($program['ProductName'])." screenshot' /></div>";
      echo "<div>".$program['LongDesc']."</div>";
      echo "<div style='float:left;width:100%'><div style='float:right; border:solid 1px; margin-bottom:5px; padding:2px'><a href='".$program['DirectPurchaseURL']."'>Buy Now...</a></div></div>";
      echo "<hr/><small>Powered by: <a href='http://www.download3000.com'>Free Software Downloads</a></small>";
  }
/* END view functions */     


//do_action('admin_head');  

/** Rewrite rules START **/
function rns_var_flush_rewrite() {
  global $wp_rewrite;
  $wp_rewrite->flush_rules();
  //echo "<pre>"; print_r($wp_rewrite); echo "</pre>";
}
function rns_var_vars($public_query_vars) {
    $public_query_vars[] = 'id';
    $public_query_vars[] = 'op';
    $public_query_vars[] = 'pi';
    $public_query_vars[] = 'cat';
    return $public_query_vars;
}
function rns_var_add_rewrite_rules($wp_rewrite) {
  $new_rules = array(
     'softshop/category/(.*)/(.*)' => 'index.php?pagename=softshop&op=cat&id='.$wp_rewrite->preg_index(1).'&pi='.$wp_rewrite->preg_index(2),
     'softshop/category/(.*)'      => 'index.php?pagename=softshop&op=cat&id='.$wp_rewrite->preg_index(1),     
     'softshop/(.*)' => 'index.php?pagename=softshop&op=prog&id=' . $wp_rewrite->preg_index(1)
  );
  $wp_rewrite->use_trailing_slashes = 0;
  $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}
/** Rewrite rules END **/ 

// Generate META tags
function rns_title($title){
    global $program, $programs, $rns_prop, $categories;
        global $rns_prop,$post;
        if($post->post_content != '[regnowshop]') return;    
        switch (get_query_var('op')) {
            case 'cat':   $pi = get_query_var('pi');
                          if($pi == '') $pi = 1;
                          $rgn_id = get_option('rgn_aff_id');
                          $xml = getXMLPrograms(get_query_var('id'),$pi,$rgn_id);
                          $programs = getPrograms($xml);
                          $programs[0]['xml'] = $xml;
                          $rns_prop->title = " ".$programs[0]['CategoryName'];
                          $rns_prop->description = "Buy software from ".$programs[0]['CategoryName']." category";
                          $rns_prop->keywords = "buy,download,".$programs[0]['CategoryName'];
                          break;
            case 'prog':  //$rgn_id = get_option('rgn_id');
                          $xml = getXMLProgram(get_query_var('id'),get_option('rgn_aff_id'));
                          $program = getProgram($xml);
                          $rns_prop->title = "Buy ".$program['ProductName'];
                          $rns_prop->description = "Buy or download ".$program['ProductName']; 
                          $rns_prop->keywords = "buy,download,".$program['ProductName'].",trial";
                          break;
            default:      $xml = getXMLCategories();
                          $categories = getCategories($xml);
                          $rns_prop->title = "Buy software from our software store";
                          $rns_prop->description = "Buy software";
                          $rns_prop->keywords = "buy,download,trial";
        }                  
        return $rns_prop->title;
}
function rns_metas($arg) {
    global $rns_prop,$post;
    if($post->post_content != '[regnowshop]') return;    
    echo "<meta name='description' content='".$rns_prop->description."' />";
    echo "<meta name='keywords' content='".$rns_prop->keywords."' />";  
}

add_action('admin_menu', 'regnow_admin_actions');             
register_activation_hook( __FILE__, 'plugin_activate' );
register_deactivation_hook(__FILE__,'plugin_deactivate');
add_shortcode('regnowshop', 'displayShop');
add_action('admin_head-regnow-shop', 'rns_add_css');
add_action('wp_head','rns_metas');
add_action('init', 'rns_var_flush_rewrite');
add_filter('query_vars', 'rns_var_vars');
add_action('generate_rewrite_rules', 'rns_var_add_rewrite_rules');
add_filter('wp_title', 'rns_title');

?>
