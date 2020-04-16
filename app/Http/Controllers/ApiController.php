<?php

namespace App\Http\Controllers;

use App\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ApiController extends Controller
{
    protected $id;

    protected function _sendOk(array $result): array
    {
        return [
            "jsonrpc" => "2.0",
            "result" => $result,
            "id" => $this->id
        ];
    }

    protected function _sendError(int $code, string $message): array
    {
        return [
            "jsonrpc" => "2.0"
            , "error" => [
                'code' => $code,
                'message' => $message
            ],
            "id" => $this->id
        ];
    }

    public function run(Request $request)
    {
        $this->id = $request->input('id');
        $method = $request->input('method');
        $params = $request->input('params');
        return $this->_runMethod($method, $params);
    }

    protected function _runMethod(string $method, array $params): array
    {
        $result = null;
        $_method = '_' . $method;
        if (method_exists($this, $_method)) {
            try {
                $result = $this->$_method($params);
            } catch (\Exception $e){
                return $this->_sendError($e->getCode(), $e->getMessage());
            }
            if (is_array($result)) {
                return $this->_sendOk($result);
            }
        }
        return $this->_sendError(400, 'unknow method');
    }

    protected function _sendMessage(array $params): ?array
    {
        $message = $params['message'] ?? '';
        $message = str_replace(PHP_EOL, '<br/>',$message);
        $data = [
            'name' => $params['name'] ?? '',
            'message' => $message,
            'page_uid' => $params['page_uid'] ?? ''
        ];
        $message = new Message($data);
        $res = $message->save();
        if (!$res) return $this->_sendError('500','fail save');
        $message = Message::find($message->id);
        return [
            'created' => $message->created_at,
            'message' => $message->message,
            'name' => $message->name,
            'id' => $message->id
        ];
    }
    protected function _getMessages(array $params): ?array
    {
        $page_uid = trim($params['page_uid'] ?? '');
        if (!$page_uid) return null;
        $result = [];
        $where = Message::where(['page_uid' => $page_uid]);
        foreach ($where->cursor() as $message) {
            $result[] = [
                'created' => $message->created_at,
                'message' => $message->message,
                'name' => $message->name,
                'id' => $message->id
            ];
        }
        return $result;
    }
}
