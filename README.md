# api-doc-gen
easy tool that allow to explore API via html

# usage example

$cinema = new Rest("Cinemas", "/cinemas");
$request = array();
$header = array(
	"content-type"				=> "application/json",
	"x-rate-limit-limit"		=> "300",
	"x-rate-limit-remaining"	=> "300",
	"x-rate-limit-reset"		=> "timestamp next 15m",
	"custom-header"				=> "bla bla bla",
);
$body_success  = '["The Space Cinema", "Cine Città Fiera", "another boring cinema"]';
$body_failure = '';
$cinema->set_request($request);
$cinema->set_header($header);
$cinema->set_response(200, "application/json", $body_success);
$cinema->set_response(423, "application/json", $body_failure);

$customer = new Rest("Customer", "/customer");
$request = array(
	"fullname" => "tizio caio",
	"age" => 72,
	"gender" => "M",
);
$header = array(
	"content-type"				=> "application/json",
);
$body_success  = '[]';
$body_failure = '';
$customer->set_request($request);
$customer->set_header($header);
$customer->set_response(200, "application/json", $body_success);
$customer->set_response(400, "application/json", $body_failure);
		
$api_doc_url = 'https://apidoc.reference.com';
$api_base_url = 'https://myapp/api'
$explore = new ApiDocGen();
$explore->set_doc_url($api_doc_url);
$explore->set_endpoint_url($api_base_url);
$explore->add_rest($tickets);
$explore->add_rest($customers);
$html_api = $explore->generate();
