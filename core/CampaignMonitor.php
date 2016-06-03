<?php
namespace  core;

class CampaignMonitor
{
    protected $auth = array();
    protected static $errors = array();

    public function __construct($accessToken, $refreshToken)
    {
        $this->auth = array('access_token' => $accessToken,
            'refresh_token' => $refreshToken);
    }

    public function get_last_error(){
        if (!empty(self::$errors)){
            return end(self::$errors);
        }
        return '';
    }

    /**
     * @param array $auth override the class authentication credentials
     * @return mixed|null list of clients
     */
    public function get_clients($auth = array())
    {

        $clientsClass = Helper::getPluginDirectory('class/csrest_general.php');
        require_once $clientsClass;


        if (empty($auth)) {
            $auth = $this->auth;
        }

        $instance = new \CS_REST_General($auth);
        $result = $instance->get_clients();

        if ($result->was_successful()) {
             return $result->response;
        } else {
            // TODO log exception
            return $result->response;
           self::$errors[] = $result->response;
           //$requestResults->status_code = $result->http_status_code;
        }

        return null;
    }
    /**
     * @param array $auth override the class authentication credentials
     * @return mixed|null list of timezones
     */
    public function get_timezones($auth = array())
    {

        $clientsClass = Helper::getPluginDirectory('class/csrest_general.php');
        require_once $clientsClass;


        if (empty($auth)) {
            $auth = $this->auth;
        }

        $instance = new \CS_REST_General($auth);
        $result = $instance->get_timezones();

        if ($result->was_successful()) {
             return $result->response;
        } else {
            // TODO log exception
            return $result->response;
           self::$errors[] = $result->response;
           //$requestResults->status_code = $result->http_status_code;
        }

        return null;
    }
    /**
     * @param array $clientObject array(
                                    'CompanyName' => 'Clients company name',
                                    'Country' => 'Clients country',
                                    'Timezone' => 'Clients timezone'
                                    )
     * @param array $auth override the class authentication credentials
     * @return mixed| client id
     */
    public function create_client($clientObject, $auth = array())
    {

        $clientsClass = Helper::getPluginDirectory('class/csrest_clients.php');
        require_once $clientsClass;


        if (empty($auth)) {
            $auth = $this->auth;
        }

        $instance = new \CS_REST_Clients(NULL, $auth);
        $result = $instance->create($clientObject);

        if ($result->was_successful()) {
             return $result->response;
        } else {
            // TODO log exception
            return $result->response;
           self::$errors[] = $result->response;
           //$requestResults->status_code = $result->http_status_code;
        }

        return null;
    }

    /**
     * @param array $auth override the class authentication credentials
     * @return mixed|null list of clients
     */
    public function get_subscriber($listId, $auth = array())
    {

        $clientsClass = Helper::getPluginDirectory('class/csrest_subscribers.php');
        require_once $clientsClass;


        if (empty($auth)) {
            $auth = $this->auth;
        }

        $instance = new \CS_REST_Subscribers($listId, $auth);
        $result = $instance->get();

        if ($result->was_successful()) {
             return $result->response;
        } else {
            // TODO log exception

           self::$errors[] = $result->response;
           //$requestResults->status_code = $result->http_status_code;
        }

        return null;
    }
    /**
     * @param array $auth override the class authentication credentials
     * @return mixed|null list of clients
     */
    public function get_stats($listId, $auth = array())
    {

        $clientsClass = Helper::getPluginDirectory('class/csrest_lists.php');
        require_once $clientsClass;


        if (empty($auth)) {
            $auth = $this->auth;
        }

        $instance = new \CS_REST_Lists($listId, $auth);
        $result = $instance->get_stats();

        if ($result->was_successful()) {
             return $result->response;
        } else {
            // TODO log exception

           self::$errors[] = $result->response;
           //$requestResults->status_code = $result->http_status_code;
        }

        return null;
    }
    /**
     * @param array $auth override the class authentication credentials
     * @return mixed|null list of clients
     */
    public function get_list_details($listId, $auth = array())
    {
        $clientsClass = Helper::getPluginDirectory('class/csrest_lists.php');
        require_once $clientsClass;


        if (empty($auth)) {
            $auth = $this->auth;
        }

        $instance = new \CS_REST_Lists($listId, $auth);
        $result = $instance->get();

        if ($result->was_successful()) {
             return $result->response;
        } else {
           Log::write($result->response);
           self::$errors[] = $result->response;
        }

        return null;
    }

    /**
     * @param array $auth override the class authentication credentials
     * @return mixed|null list of clients
     */
    public function send_email($message,  $auth = array())
    {

        $clientsClass = Helper::getPluginDirectory('class/csrest_transactional_smartemail.php');
        require_once $clientsClass;


        if (empty($auth)) {
            $auth = $this->auth;
        }

        $apiKey = '0417f2ed02c1adf2e0f0d84a326b9292b6899e46f11c6218';
        $clientID = '0ee2824b00e76e154b25aff6d2e272ea';
        $emailID = '3de23fa0-3a97-41a0-8c33-a1ca305e4ed5';

        $instance = new \CS_REST_Transactional_SmartEmail($emailID, array('api_key' => $apiKey));


        $add_recipients_to_subscriber_list = false;
        $result = $instance->send($message, $add_recipients_to_subscriber_list);

        if ($result->was_successful()) {
             return $result->response;
        } else {
            // TODO log exception
            return $result->response;
           self::$errors[] = $result->response;
           //$requestResults->status_code = $result->http_status_code;
        }

        return null;
    }
    /**
     * @param array $auth override the class authentication credentials
     * @return mixed|null list of clients
     */
    public function import_subscribers($listId, $data, $auth = array())
    {

        $clientsClass = Helper::getPluginDirectory('class/csrest_subscribers.php');
        require_once $clientsClass;


        if (empty($auth)) {
            $auth = $this->auth;
        }

        $instance = new \CS_REST_Subscribers($listId, $auth);
        $result = $instance->import($data, false);

        if ($result->was_successful()) {
             return $result->response;
        } else {
            // TODO log exception
            return $result->response;
            echo 'Failed with code '.$result->http_status_code."\n<br /><pre>";
            var_dump($result->response);
            echo '</pre>';

            if($result->response->ResultData->TotalExistingSubscribers > 0) {
                echo 'Updated '.$result->response->ResultData->TotalExistingSubscribers.' existing subscribers in the list';
            } else if($result->response->ResultData->TotalNewSubscribers > 0) {
                echo 'Added '.$result->response->ResultData->TotalNewSubscribers.' to the list';
            } else if(count($result->response->ResultData->DuplicateEmailsInSubmission) > 0) {
                echo $result->response->ResultData->DuplicateEmailsInSubmission.' were duplicated in the provided array.';
            }

            echo 'The following emails failed to import correctly.<pre>';
            var_dump($result->response->ResultData->FailureDetails);
           self::$errors[] = $result->response;

        }

        return null;
    }

    /**
     * @param string $listId
     * @param string $name
     * @param array $segmentsRules to for this segment of the form array(array('RuleType' => '', 'Clause' => ''))
     * @param array $auth
     * @return null
     */
    public function create_segment($listId, $segment, $auth = array())
    {

        $clientsClass = Helper::getPluginDirectory('class/csrest_segments.php');
        require_once $clientsClass;


        if (empty($auth)) {
            $auth = $this->auth;
        }

//        $segmentOptions = array('Title' => $name,'RuleGroups' => array());

        $instance = new \CS_REST_Segments(NULL, $auth);


        $result = $instance->create($listId, $segment);

        if ($result->was_successful()) {
             return $result->response;
        } else {
            // TODO log exception
            return $result->response;
           self::$errors[] = $result->response;
           //$requestResults->status_code = $result->http_status_code;
        }

        return null;
    }

    /**
     * @param $clientId for which client to get the lists
     * @param array $auth override the class authentication credentials
     * @return mixed|null list of clients
     */
    public function update_custom_field($listId,$fieldKey, $fieldName, $visibleInPreferenceCenter = true, $auth = array())
    {

        $clientsClass = Helper::getPluginDirectory('class/csrest_lists.php');
        require_once $clientsClass;
        $requestResults = new \stdClass();

        if (empty($auth)) {
            $auth = $this->auth;
        }

        $instance = new \CS_REST_Lists($listId, $auth);
        $params = array(
            'FieldName' => $fieldName,
            'VisibleInPreferenceCenter'=> $visibleInPreferenceCenter
        );
        $result = $instance->update_custom_field($fieldKey, $params);

        if ($result->was_successful()) {
             return $result->response;
        } else {
            // TODO log exception
            $result->response->name = $fieldName;
            Log::write($result->response);
           self::$errors[] = $result->response;
           //$requestResults->status_code = $result->http_status_code;
        }

        return null;
    }
    /**
     * @param $clientId for which client to get the lists
     * @param array $auth override the class authentication credentials
     * @return mixed|null list of clients
     */
    public function create_custom_field($listId,$fieldName, $dataType, $options = array(), $visibleInPreferenceCenter = true, $auth = array())
    {

        $clientsClass = Helper::getPluginDirectory('class/csrest_lists.php');
        require_once $clientsClass;
        $requestResults = new \stdClass();

        if (empty($auth)) {
            $auth = $this->auth;
        }

        $instance = new \CS_REST_Lists($listId, $auth);
        $params = array(
            'FieldName' => $fieldName,
            'DataType' => $dataType,
            'Options' => $options,
            'VisibleInPreferenceCenter'=> $visibleInPreferenceCenter
        );
        $result = $instance->create_custom_field($params);

        if ($result->was_successful()) {
             return $result->response;
        } else {
            // TODO log exception
            $result->response->name = $fieldName;
            Log::write($result->response);
           self::$errors[] = $result->response;
           //$requestResults->status_code = $result->http_status_code;
        }

        return null;
    }

    /**
     * @param $clientId for which client to get the lists
     * @param array $auth override the class authentication credentials
     * @return mixed|null list of clients
     */
    public function get_client_list($clientId, $auth = array())
    {

        $clientsClass = Helper::getPluginDirectory('class/csrest_clients.php');
        require_once $clientsClass;
        $requestResults = new \stdClass();

        if (empty($auth)) {
            $auth = $this->auth;
        }

        $clients = new \CS_REST_Clients($clientId, $auth);
        $result = $clients->get_lists();

        if ($result->was_successful()) {
             return $result->response;
        } else {
            // TODO log exception

           self::$errors[] = $result->response;
           //$requestResults->status_code = $result->http_status_code;
        }

        return null;
    }

    /**
     * @param $clientId for which to whom create the list
     * @param array $auth override the class authentication credentials
     * @return mixed|null list id
     */
    public function create_list($clientId, $listTitle, $confirmedOptIn = false, $unsubscribePage = '', $confirmationPage = '', $auth = array())
    {

        $clientsClass = Helper::getPluginDirectory('class/csrest_lists.php');
        require_once $clientsClass;
        $requestResults = new \stdClass();

        if (empty($auth)) {
            $auth = $this->auth;
        }

        $instance = new \CS_REST_Lists(NULL, $auth);

        $listOptions = array(
            'Title' => $listTitle,
            'UnsubscribePage' => $unsubscribePage,
            'ConfirmedOptIn' => $confirmedOptIn,
            'ConfirmationSuccessPage' => $confirmationPage,
            'UnsubscribeSetting' => CS_REST_LIST_UNSUBSCRIBE_SETTING_ALL_CLIENT_LISTS
        );

        $result = $instance->create($clientId, $listOptions );

        if ($result->was_successful()) {
             return $result->response;
        } else {
            // TODO log exception
            return  $result->response;
           self::$errors[] = $result->response;
           //$requestResults->status_code = $result->http_status_code;
        }

        return null;
    }

    /**
     *
     * @param $listId the id for which you want the custom fields from
     * @param array $auth override the class authentication credentials
     * @return mixed|null custom field list
     */
    public function get_custom_fields($listId, $auth = array())
    {
        $clientsClass = Helper::getPluginDirectory('class/csrest_lists.php');
        require_once $clientsClass;
        $requestResults = new \stdClass();

        if (empty($auth)) {
            $auth = $this->auth;
        }

        $customFields = new \CS_REST_Lists($listId, $auth );
        $result = $customFields->get_custom_fields();

        if ($result->was_successful()) {
             return $result->response;
        } else {
            // TODO log exception
           self::$errors[] = $result->response;
           //$requestResults->status_code = $result->http_status_code;
        }

        return null;
    }

    /**
     *
     * @param $listId the id for which you want to get segments for
     * @param array $auth override the class authentication credentials
     * @return mixed|null segments list
     */
    public function get_segments($listId, $getDetails = false, $auth = array())
    {
        $clientsClass = Helper::getPluginDirectory('class/csrest_lists.php');
        require_once $clientsClass;
        $requestResults = new \stdClass();

        if (empty($auth)) {
            $auth = $this->auth;
        }

        $customFields = new \CS_REST_Lists($listId, $auth );
        $result = $customFields->get_segments();

        if ($result->was_successful()) {
              $segments = $result->response;

            if (!empty($segments)){
                if ($getDetails){
                    foreach ($segments as $segment){
                        $details = $this->get_segment($segment->SegmentID);
                        if (null != $details){
                            $segment->Details = $details;
                        }
                    }
                    return $segments;
                } else {
                    return $segments;
                }
            }
        } else {
            // TODO log exception
           self::$errors[] = "Error trying to get segments: ". $result->response;
           //$requestResults->status_code = $result->http_status_code;
        }

        return null;
    }
    /**
     *
     * @param $listId the id for which you want to get segments for
     * @param array $auth override the class authentication credentials
     * @return mixed|null segments list
     */
    public function get_segment($segmentID, $auth = array())
    {
        $clientsClass = Helper::getPluginDirectory('class/csrest_segments.php');
        require_once $clientsClass;
        $requestResults = new \stdClass();

        if (empty($auth)) {
            $auth = $this->auth;
        }

        $segmentInstance = new \CS_REST_Segments($segmentID, $auth);
        $result = $segmentInstance->get();

        if ($result->was_successful()) {
            $segment = $result->response;
            return $segment;
        } else {
            // TODO log exception
           self::$errors[] = "Error trying to get segment with id:$segmentID  ". $result->response;
           //$requestResults->status_code = $result->http_status_code;
        }

        return null;
    }


    function get_list_segments($listId, $include_details=0)
    {
        $wrap = $this->get_wrap_list($listId);

        $result = $wrap->get_segments();

        $return_val = $this->process_api_return($result);


        if ($include_details && is_array($return_val))
        {
            foreach ($return_val as $k=>$seg)
            {
                //sr_dump($seg);

                $result=$this->get_segment_details($seg->SegmentID);
                if (is_object($result))
                {
                    if (!empty($result->SegmentID))
                    {
                        $return_val[$k]->details = $result;
                    }
                }
                //sr_dump($return_val);
            }
        }

        return $return_val;
    }
}
