<?php
/**
 * dato in input la struttura di un certo tipo di API
 * si occupa di eseguire un render html in modo tale da rendere esplorabile
 * e consultabile all'utente
 * 
 * @author alessio
 *
 */

class ApiDocGen{
	
	private $rest = array();
	private $html_method_block_clean = '
		<div id="%DIV_ID%">
			
			<h3><span style="color: #4F81BD;">%TITLE% <a title="go to table of content" href="%API_DOC_BASE_URL%#table-of-contents"><i class="icon-chevron-sign-up"></i></a></span></h3>
			
			<!-- REQUEST -->
			<div class="widget widget-table">
				<div class="widget-header">
					<h3>Request</h3>
				</div>
				<div class="widget-content">
					<table class="table table-bordered table-hover">
						<thead>
							<tr>
								<th>Method</th>
								<th>POST</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>URL</td>
								<td>%URL%</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			
			<!-- URL PARAMS -->
			<div class="widget widget-table">
				<div class="widget-header">
					<h3>Url Params</h3>
				</div>
				<div class="widget-content">
					<table class="table table-bordered table-hover">
						<thead>
							<tr>
								<th>Param Name</th>
								<th>Param Value</th>
							</tr>
						</thead>
						<tbody>
							%REQUEST_PARAMS%
						</tbody>
					</table>
				</div>
			</div>

			<!-- HEADERS -->
			<div class="widget widget-table">
				<div class="widget-header">
					<h3>Headers</h3>
				</div>
				<div class="widget-content">
					<table class="table table-bordered table-hover">
						<thead>
							<tr>
								<th>Header Name</th>
								<th>Header Value</th>
							</tr>
						</thead>
						<tbody>
							%HEADERS%
						</tbody>
					</table>
				</div>
			</div>

			<!-- SUCCESS RESPONSE HEADER -->
			<div class="widget widget-table">
				<div class="widget-header widget-success">
					<h3>Success Response Header</h3>
				</div>
				<div class="widget-content">
					<table class="table table-bordered table-hover table-success">
						<tbody>
							%SUCCESS_RESPONSE_HEADER%
						</tbody>
					</table>
				</div>
			</div>

			<!-- UNSUCCESSFUL RESPONSE HEADER -->
			<div class="widget widget-table">
				<div class="widget-header widget-failure">
					<h3>Unsuccess Response Header</h3>
				</div>
				<div class="widget-content">
					<table class="table table-bordered table-hover table-failure">
						<tbody>
							%UNSUCCESSFUL_RESPONSE_HEADER%
						</tbody>
					</table>
				</div>
			</div>
		</div>';
	
	private $api_base_url;
	private $api_doc_url;
	
	public function __construct($api_doc_url){
		if(APP_LOCATION === APP_LOCATION_DEVELOPMENT){
			$p = 'http://';
			$api_base_url = "http://nidoma.local/api";
		}elseif(APP_LOCATION === APP_LOCATION_STAGING){
			$p = 'https://';
			$api_base_url = "https://test.nidoma.com/api";
		}else{
			$p = 'https://';
			$api_base_url = "https://nidoma.com/api";
		}
		$this->api_base_url = $api_base_url;
		$this->api_doc_url = $p . SERVER_NAME . $api_doc_url;
	}
	
	/**
	 * @param Rest $rest
	 */
	public function add_rest($rest){
		$this->rest[] = $rest;
	}
	
	/**
	 * @return string
	 */
	public function generate(){
		
		$api_base_url = $this->api_base_url;
		$api_doc_base_url = $this->api_doc_url;
		
		// let's do something, building html api doc!
		$html_list_block = $html_div_block = array();
		foreach($this->rest as $n => $rest){
			
			$html = '';
			$data = $rest->to_array();
			
			$html_method_block_clean = str_replace('%API_DOC_BASE_URL%', $api_doc_base_url, $this->html_method_block_clean);
			
			$html_request_params = '';
			foreach($data['request_params'] as $k => $v){
				$html_request_params .= "<tr><td>$k</td><td>$v</td></tr>" . PHP_EOL;
			}
			
			$html_request_header = '';
			foreach($data['headers'] as $k => $v){
				$html_request_header .= "<tr><th>$k</th><td>$v</td></tr>" . PHP_EOL;
			}
			
			$html_response_success = '';
			foreach($data['response']['success'] as $k => $v){
				if($k == 'body'){
					$colspan = 'colspan="2"';
					$html_response_success .= "<tr><th $colspan>$k</th></tr>"
						. "<tr><td $colspan><pre>$v</pre></td></tr>";
				}else{
					$html_response_success .= "<tr><th>$k</th><td>$v</td></tr>";
				}
				$html_response_success .= PHP_EOL;
			}
			
			// TODO: maybe here can be regrouped by status codes
			$html_response_failure = '';
			foreach($data['response']['failure'] as $failure){
				foreach($failure as $k => $v){
					if($k == 'body'){
						$colspan = 'colspan="2"';
						$html_response_failure .= "<tr><th $colspan>$k</th></tr>"
							. "<tr><td $colspan><pre>$v</pre></td></tr>";
					}else{
						$html_response_failure .= "<tr><th>$k</th><td>$v</td></tr>";
					}
					$html_response_failure .= PHP_EOL;
				}
			}
			
			$div_id = str_replace(" ", "-", strtolower($data['method_name']));
			$search = array(
				'%DIV_ID%',
				'%TITLE%',
				'%URL%',
				'%REQUEST_PARAMS%',
				'%HEADERS%',
				'%SUCCESS_RESPONSE_HEADER%',
				'%UNSUCCESSFUL_RESPONSE_HEADER%',
			);
			$replace = array(
				$div_id,
				$data['method_name'],
				$api_base_url . $data['request_url'],
				$html_request_params,
				$html_request_header,
				$html_response_success,
				$html_response_failure,
			);
			
			$html = str_replace($search, $replace, $html_method_block_clean);
			$html_div_block[]	= $html;
			$html_list_block[]	= "<a title=\"go to method specs\" href=\"" . $api_doc_base_url . "#$div_id\">" . $data['method_name'] . "</a>";
			
		}
		
		$html_list = ''
			. '<div id="table-of-contents">'
				. '<h3><span style="color: #4F81BD;">Table of contents</span></h3>'
				. '<ol><li>' . implode("</li>".PHP_EOL."<li>", $html_list_block) . "</li></ol>"
			. "</div>"
			. "<hr>";
		$html_divs = implode(PHP_EOL . "<br><hr><br>", $html_div_block);
		$content = $html_list . $html_divs;
		
		return $content;
		
	}
	
}

class Rest{
	
	private $request  = array();
	private $response = array();
	private $header   = array();
	public  $name;
	public  $url;
	
	/**
	 * @param string $name
	 * @param string $url
	 */
	public function __construct($name, $url){
		$url = strpos($url, '/') === 0 ? $url : '/'.$url;
		$this->name = ucwords($name);
		$this->url	= $url;
	}
	
	/**
	 * @param int $code
	 * @param string $content_type
	 * @param array|string $body
	 */
	public function set_response($code, $content_type, $body){
		$date = date("D, d M Y H:i:s O"); //  RFC 1123
		$pretty_body = is_array($body) ? json_encode($body, JSON_PRETTY_PRINT) : json_encode(json_decode($body), JSON_PRETTY_PRINT);
		if(empty($pretty_body)){ // compatibility php < 5.4
			$pretty_body = is_array($body) ? $this->json_format(json_encode($body)) : $this->json_format(json_encode(json_decode($body)));
		}
		$pretty_body = $pretty_body == 'null' ? '[]' : $pretty_body;
		$response = array(
			"status_code"		=> $code . " " . Util::get_http_status_message($code),
			"content_type"		=> $content_type,
			"date"				=> $date,
			"body"				=> $pretty_body,
		);
		$type = $code == 200 ? 'success' : 'failure';
		$this->response[$type][] = $response;
	}
	
	/**
	 * @param array $request
	 */
	public function set_request($request){
		$this->request = $request;
	}
	
	/**
	 * @param array $request
	 */
	public function set_header($header){
		$this->header = $header;
	}
	
	/**
	 * @param string $type_successful
	 * @return array
	 */
	public function get_response($type_successful){
		$type = $type_successful == 1 ? 'success' : 'failure';
		return $this->response[$type];
	}
	
	/**
	 * @return array
	 */
	public function get_request(){
		return $this->request;
	}
	
	/**
	 * @return array
	 */
	public function get_header(){
		return $this->header;
	}
	
	/**
	 * @return array
	 */
	public function to_array(){
		$data = array();
		$data['method_name']			= $this->name;
		$data['request_url']			= $this->url;
		$data['request_params']			= $this->get_request();
		$data['headers']				= $this->get_header();
		$data['response']['success']	= array_pop($this->get_response(1));
		$data['response']['failure']	= $this->get_response(0);
		return $data;
	}
	
	private function json_format($json){
		$tab = "  ";
		$new_json = "";
		$indent_level = 0;
		$in_string = false;

		$json_obj = json_decode($json);

		if($json_obj === false)
			return false;

		$json = json_encode($json_obj);
		$len = strlen($json);

		for($c = 0; $c < $len; $c++)
		{
			$char = $json[$c];
			switch($char)
			{
				case '{':
				case '[':
					if(!$in_string)
					{
						$new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
						$indent_level++;
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case '}':
				case ']':
					if(!$in_string)
					{
						$indent_level--;
						$new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case ',':
					if(!$in_string)
					{
						$new_json .= ",\n" . str_repeat($tab, $indent_level);
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case ':':
					if(!$in_string)
					{
						$new_json .= ": ";
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case '"':
					if($c > 0 && $json[$c-1] != '\\')
					{
						$in_string = !$in_string;
					}
				default:
					$new_json .= $char;
					break;                   
			}
		}

		return $new_json;
		
	} 
	
}