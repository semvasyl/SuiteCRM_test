<?

/**
 * The PHP class for SuiteCRM API .
 * @author Vasyl Semeniuk <semvasyl@gmail.com>
 * @version 0.1
 */

class SuiteCRM
{
    /**
     * SuiteCRM url.
     * @var string
     */
	public $url;

    /**
     * SuiteCRM username.
     * @var string
     */
	public $username;
	
    /**
     * SuiteCRM password.
     * @var string
     */
	public $password;
	
    /**
     * SuiteCRM application_name.
     * @var string
     */
	public $application_name;
	
    /**
     * SuiteCRM login_parameters.
     * @var array
     */
	public $login_parameters;
	
    /**
     * SuiteCRM login_result.
     * @var resourse
     */
	public $login_result;
	
    /**
     * SuiteCRM session_id.
     * @var string
     */	
	public $session_id;
	
    /**
     * SuiteCRM url.
     * @var string
     */
	public $moduleName;
	
    /**
     * SuiteCRM url.
     * @var string
     */
	public $moduleCols;

	
    /**
     * Constructor.
     * @param   string  $url
     * @param   string  $username
     * @param   string  $password
     * @param   string  $application_name
     * @return  void
     */
	function __construct($url,$username,$password,$application_name="RestTest")
	{
		$this->url=$url;
		$this->username=$username;
		$this->password=$password;
		$this->login_parameters=array(
			"user_auth" => array(
			"user_name" => $this->username,
			"password" => md5($this->password),
			"version" => "1"
			),
			"application_name" => $this->application_name,
			"name_value_list" => array(),
			);
		$this->moduleName="NoName";
		$this->moduleCols[][]=array();
		$this->login_result="";

	}


    /**
     * Connect with API.
     * @param   string  $method
     * @param   array   $parameters
     * @param   string  $url
     * @return  resourse
     */
	public function call($method, $parameters, $url)
	{
		ob_start();
		$curl_request = curl_init();

		curl_setopt($curl_request, CURLOPT_URL, $url);
		curl_setopt($curl_request, CURLOPT_POST, 1);
		curl_setopt($curl_request, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($curl_request, CURLOPT_HEADER, 1);
		curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_request, CURLOPT_FOLLOWLOCATION, 0);

		$jsonEncodedData = json_encode($parameters);

		$post = array(
		"method" => $method,
		"input_type" => "JSON",
		"response_type" => "JSON",
		"rest_data" => $jsonEncodedData
		);

		curl_setopt($curl_request, CURLOPT_POSTFIELDS, http_build_query($post));
		$result = curl_exec($curl_request);
		$http_status = curl_getinfo($curl_request, CURLINFO_HTTP_CODE);
		//--------------------------------------------
		    // echo "<br/>result from url: ".$url." <br/>";
		    // print_r($http_status);
		    // echo "----<br/>";
		    // print_r($result);
		//--------------------------------------------

		curl_close($curl_request);

		$result = explode("\r\n\r\n", $result, 2);
		$response = json_decode($result[1]);
		ob_end_flush();
		return $response;
	}

    /**
     * Render data on page.
     * @param   string  $moduleName
     * @param   array   $result
     * @return  void
     */
	public function DisplayValues($moduleName="",$result=array())
	{

		?>
		<br/>
		<h2>Module: <? echo $moduleName; ?></h2>
		<table border="3px">
		    <thead>
		        <?
		            echo "<tr><td>#</td>";
		            foreach ($result->entry_list[0]->name_value_list as $key) {
		                echo "<td>".$key->name."</td>";
		            }
		            echo "<td>email</td></tr>";        
		        ?>    
		    </thead>
		    <tbody>
		        <?
		        for ($i=0; $i < $result->result_count; $i++) {
		        	$j=$i+1;
		            echo "<tr><td>".$j."</td>";
		            foreach ($result->entry_list[$i]->name_value_list as $key) {
		                echo "<td>".$key->value."</td>";
		            }
		            echo "<td>".$result->relationship_list[$i]->link_list[0]->records[0]->link_value->email_address->value."</td></tr>";
		        }
		        ?>
		    </tbody>
		</table>
		<?
	}

    /**
     * Get Data from API by ModuleName.
     * @param   string  $moduleName
     * @return  void
     */
	public function GetModuleData($moduleName)
	{
	    $get_entry_list_parameters = array(
	         //session id
	         'session' => $this->session_id,

	         //The name of the module from which to retrieve records
	         'module_name' => $this->moduleCols[$moduleName]["name"],

	         //The SQL WHERE clause without the word "where".
	         'query' => "",

	         //The SQL ORDER BY clause without the phrase "order by".
	         'order_by' => "",

	         //The record offset from which to start.
	         'offset' => 0,

	         //A list of fields to include in the results.
	         'select_fields' => $this->moduleCols[$moduleName]["params"],
	         //A list of link names and the fields to be returned for each link name.
	         'link_name_to_fields_array' => array(
	             array(
	                 'name' => 'email_addresses',
	                 'value' => array(
	                     'email_address',
	                     'opt_out',
	                     'primary_address'
	                 ),
	             ),
	         ),
	         //The maximum number of results to return.
	         'max_results' => 20,

	         //If deleted records should be included in results.
	         'deleted' => 0,

	         //If only records marked as favorites should be returned.
	         'favorites' => false,
	    );

	    $res = $this->call("get_entry_list", $get_entry_list_parameters, $this->url);

	    $this->DisplayValues($moduleName,$res);
		
	}

    /**
     * Get Session ID.
     * @return  void
     */
	public function auth()
	{
		$this->login_result = $this->call("login", $this->login_parameters, $this->url);
		$this->session_id = $this->login_result->id;
	}

}


$crm = new SuiteCRM("http://1110134.semvasyl.web.hosting-test.net/SuiteCRM/service/v3_1/rest.php","Vasyl","Glori@");

$crm->auth();


$crm->moduleCols["Accounts"]["name"]="Accounts";
$crm->moduleCols["Accounts"]["params"]=array('name','phone_office','website','billing_address_country');
$crm->moduleCols["Contacts"]["name"]="Contacts";
$crm->moduleCols["Contacts"]["params"]=array('name','phone_office','website','title','department');
$crm->moduleCols["Leads"]["name"]="Leads";
$crm->moduleCols["Leads"]["params"]=array('first_name','last_name','phone_work','primary_address_city','title','department');
$crm->moduleCols["Tasks"]["name"]="Tasks";
$crm->moduleCols["Tasks"]["params"]=array('name','status','date_due','created_by');
$crm->moduleCols["Opportunities"]["name"]="Opportunities";
$crm->moduleCols["Opportunities"]["params"]=array('name','opportunity_type','amount','	sales_stage');


$crm->GetModuleData("Accounts");
$crm->GetModuleData("Contacts");
$crm->GetModuleData("Leads");
$crm->GetModuleData("Tasks");
$crm->GetModuleData("Opportunities");

?>