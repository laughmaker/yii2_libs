<?php
/**
 * Copyright (C) Alibaba Cloud Computing
 * All rights reserved
 */

namespace mmgg\utils;

use mmgg\traits\Param;

class AliyunLog
{
    use Param;

    const TOPIC_ACCESS = 'access';
    const TOPIC_WARNING = 'warning';
    const TOPIC_ERROR = 'error';

    private $_endpoint;
    private $_accessKeyId;
    private $_accessKeySecret;
    private $_project;
    private $_logStore;
    private $_token;

    /**
     * @var \Aliyun_Log_Client
     */
    private $_client;

    public function __construct()
    {
        $ossConfig = $this->getLocalParam('aliyunLog');
        $this->_endpoint = $ossConfig['endpoint'];
        $this->_accessKeyId = $ossConfig['accessKeyId'];
        $this->_accessKeySecret = $ossConfig['accessKeySecret'];
        $this->_project = $ossConfig['project'];
        $this->_logStore = $ossConfig['logStore'];
        $this->_token = $ossConfig['token'];

        $this->_client = new \Aliyun_Log_Client($this->_endpoint, $this->_accessKeyId, $this->_accessKeySecret, $this->_token);
    }

    /**
     * 上传日志
     * @param array|string $data
     * @param int $status
     * @return \Aliyun_Log_Exception|\Aliyun_Log_Models_PutLogsResponse|\Exception
     */
    public function putLog($data, int $status=SUCCESS)
    {
        $topic = AliyunLog::TOPIC_WARNING;
        if ($status === SERVER_ERROR) {
            $topic = AliyunLog::TOPIC_ERROR;
        } elseif ($status === SUCCESS) {
            $topic = AliyunLog::TOPIC_ACCESS;
        }

        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_SLASHES);
        }

        $contents = [
            'post' => json_encode($_POST, JSON_UNESCAPED_SLASHES),
            'get' => json_encode($_GET, JSON_UNESCAPED_SLASHES),
            'host' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '',
            'action' => isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '',
            'ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            'userAgent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'server' => json_encode($_SERVER, JSON_UNESCAPED_SLASHES),
            'data' => $data,
        ];

        $logItem = new \Aliyun_Log_Models_LogItem();
        $logItem->setTime(time());
        $logItem->setContents($contents);
        $logItems = [$logItem];
        $request = new \Aliyun_Log_Models_PutLogsRequest($this->_project, $this->_logStore, $topic, null, $logItems);

        try {
            $response = $this->_client->putLogs($request);
            return $response;
        } catch (\Aliyun_Log_Exception $ex) {
            return $ex;
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    public function listLogStores()
    {
        try {
            $request = new \Aliyun_Log_Models_ListLogstoresRequest($this->_project);
            $response = $this->_client->listLogstores($request);
            var_dump($response);
        } catch (\Aliyun_Log_Exception $ex) {
            var_dump($ex);
        } catch (\Exception $ex) {
            var_dump($ex);
        }
    }

    public function listTopics()
    {
        $request = new \Aliyun_Log_Models_ListTopicsRequest($this->_project, $this->_logStore);
        try {
            $response = $this->_client->listTopics($request);
            var_dump($response);
        } catch (\Aliyun_Log_Exception $ex) {
            var_dump($ex);
        } catch (\Exception $ex) {
            var_dump($ex);
        }
    }

    public function getLogs()
    {
        $topic = 'TestTopic';
        $from = time()-3600;
        $to = time();
        $request = new \Aliyun_Log_Models_GetLogsRequest($this->_project, $this->_logStore, $from, $to, $topic, '', 100, 0, false);

        try {
            $response = $this->_client->getLogs($request);
            foreach ($response -> getLogs() as $log) {
                print $log->getTime()."\t";
                foreach ($log->getContents() as $key => $value) {
                    print $key.":".$value."\t";
                }
                print "\n";
            }
        } catch (\Aliyun_Log_Exception $ex) {
            var_dump($ex);
        } catch (\Exception $ex) {
            var_dump($ex);
        }
    }

    public function getHistograms($topic)
    {
        $from = time()-3600;
        $to = time();
        $request = new \Aliyun_Log_Models_GetHistogramsRequest($this->_project, $this->_logStore, $from, $to, $topic, '');

        try {
            $response = $this->_client->getHistograms($request);
            var_dump($response);
        } catch (\Aliyun_Log_Exception $ex) {
            var_dump($ex);
        } catch (\Exception $ex) {
            var_dump($ex);
        }
    }

    /**
     * 返回 Aliyun_log_models_shard对象
     * @return mixed
     */
    public function listShard()
    {
        $request = new \Aliyun_Log_Models_ListShardsRequest($this->_project, $this->_logStore);
        try {
            $response = $this->_client -> listShards($request);
            $list = $response->getShards();
            return $list;
        } catch (\Aliyun_Log_Exception $ex) {
            var_dump($ex);
        } catch (\Exception $ex) {
            var_dump($ex);
        }
    }

    public function batchGetLogs()
    {
        $listShardRequest = new \Aliyun_Log_Models_ListShardsRequest($this->_project, $this->_logStore);
        $listShardResponse = $this->_client -> listShards($listShardRequest);
        foreach ($listShardResponse-> getShardIds()  as $shardId) {
            $getCursorRequest = new \Aliyun_Log_Models_GetCursorRequest($this->_project, $this->_logStore, $shardId, null, time() - 60);
            $response = $this->_client -> getCursor($getCursorRequest);
            $cursor = $response-> getCursor();
            $count = 100;
            while (true) {
                $batchGetDataRequest = new \Aliyun_Log_Models_BatchGetLogsRequest($this->_project, $this->_logStore, $shardId, $count, $cursor);
                var_dump($batchGetDataRequest);
                $response = $this->_client -> batchGetLogs($batchGetDataRequest);
                if ($cursor == $response -> getNextCursor()) {
                    break;
                }
                $logGroupList = $response -> getLogGroupList();
                foreach ($logGroupList as $logGroup) {
                    print($logGroup->getCategory());

                    foreach ($logGroup -> getLogsArray() as $log) {
                        foreach ($log -> getContentsArray() as $content) {
                            print($content-> getKey().":".$content->getValue()."\t");
                        }
                        print("\n");
                    }
                }
                $cursor = $response -> getNextCursor();
            }
        }
    }

    public function deleteShard($shardId)
    {
        $request = new \Aliyun_Log_Models_DeleteShardRequest($this->_project, $this->_logStore, $shardId);
        try {
            $response = $this->_client -> deleteShard($request);
            var_dump($response);
        } catch (\Aliyun_Log_Exception $ex) {
            var_dump($ex);
        } catch (\Exception $ex) {
            var_dump($ex);
        }
    }

    public function mergeShard($shardId)
    {
        $request = new \Aliyun_Log_Models_MergeShardsRequest($this->_project, $this->_logStore, $shardId);
        try {
            $response = $this->_client -> mergeShards($request);
            var_dump($response);
        } catch (\Aliyun_Log_Exception $ex) {
            var_dump($ex);
        } catch (\Exception $ex) {
            var_dump($ex);
        }
    }

    public function splitShard($shardId, $midHash)
    {
        $request = new \Aliyun_Log_Models_SplitShardRequest($this->_project, $this->_logStore, $shardId, $midHash);
        try {
            $response = $this->_client -> splitShard($request);
            var_dump($response);
        } catch (\Aliyun_Log_Exception $ex) {
            var_dump($ex);
        } catch (\Exception $ex) {
            var_dump($ex);
        }
    }
}
