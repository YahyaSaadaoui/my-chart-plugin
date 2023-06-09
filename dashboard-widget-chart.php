<?php

/** 
 * Plugin Name:       dashboard-widget-chart    
 * Description:         This is a plugin created to add a dashboard widget , graph using rechart library.
 * Version:               1.0.0
 * Requires PHP:    8.0
 * Author:                Yahya saadaoui
 * License:              GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */
function dashboard_widget_chart_enqueue_scripts()
{
    // Enqueue Recharts library
    wp_enqueue_script('recharts', 'https://unpkg.com/recharts/umd/Recharts.min.js', array('react', 'react-dom'));

    // Enqueue React and ReactDOM libraries
    wp_enqueue_script('react', 'https://unpkg.com/react/umd/react.development.js');
    wp_enqueue_script('react-dom', 'https://unpkg.com/react-dom/umd/react-dom.development.js');
    wp_enqueue_script('react-js', 'https://unpkg.com/prop-types/prop-types.min.js');
    wp_enqueue_script('recharts', 'https://unpkg.com/recharts/umd/Recharts.js');
    // Enqueue our custom script
    wp_enqueue_script('dashboard-widget-chart', plugins_url('assets/js/dashboard-widget-chart.js', __FILE__), array('recharts', 'react', 'react-dom','react-js'), '1.0', true);

    // Enqueue our custom stylesheet
    wp_enqueue_style('dashboard-widget-chart-style', plugins_url('assets/css/dashboard-widget-chart.css', __FILE__), array(), '1.0');
}
add_action('admin_enqueue_scripts', 'dashboard_widget_chart_enqueue_scripts');


function dashboard_widget_chart_init()
{
    wp_add_dashboard_widget(
        'dashboard_widget_chart',
        'Dashboard Widget Chart',
        'dashboard_widget_chart_display'
    );
}
add_action('wp_dashboard_setup', 'dashboard_widget_chart_init');

$response = wp_remote_get('http://localhost:8080/wordpress/wp-json/DWC/v1/chart-data');
if (is_wp_error($response)) {
    echo 'error';
}
$chart_data = json_decode(wp_remote_retrieve_body($response), true);
if (!is_array($chart_data)) {
    echo "error";
}
$chart_data_json = json_encode($chart_data);


function dashboard_widget_chart_display()
{
?>
    <div class="dashboard-widget-chart-container">
        <h2>Chart</h2>
        <select id="dashboard-widget-chart-select">
            <option value="7">Last 7 Days</option>
            <option value="15">Last 15 Days</option>
            <option value="30">Last Month</option>
        </select>
        <div class="dashboard-widget-chart-container" id="dashboard-widget-chart-container" data-data="<?php echo htmlspecialchars($chart_data_json); ?>"></div>
    </div>
<?php
}
// Register custom REST API endpoint for chart data
add_action('rest_api_init', 'register_custom_chart_data_endpoint');

function register_custom_chart_data_endpoint()
{
    register_rest_route('DWC/v1', '/chart-data/(?P<days>[a-zA-Z0-9-]+)', array(
        'methods' => 'GET',
        'callback' => 'get_chart_data',
    ));
}

// Callback function to retrieve chart data
function get_chart_data($request)
{
    $days = $request->get_param('days');
    $date_query = array(
        'after' => $days . ' days ago',
        'inclusive' => true,
    );
    $posts = get_posts(array(
        'date_query' => array($date_query),
        'post_type' => 'post',
        'posts_per_page' => -1,
    ));
    $data = array();
    foreach ($posts as $post) {
        $data[] = array(
            'date' => get_the_date('Y-m-d', $post->ID),
            'views' => get_post_meta($post->ID, 'post_views_count', true),
        );
    }
    return $data;
}
