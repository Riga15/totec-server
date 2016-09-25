<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Response;
use Phalcon\Mvc\Micro;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {
    $di = new FactoryDefault(); // frameworkで用意されているモジュールを使用する
    include APP_PATH . '/config/services.php'; // モジュールの追加、上書きする
    $config = $di->getConfig(); // 設定を取得する
    include APP_PATH . '/config/loader.php'; // AutoLoadのディレクトリを登録する

    $app = new Micro($di); // Microフレームワークを使用する

    /*
     * リクエストの共通処理
     * falseを返却した場合はリクエストは処理されない
     */
    $app->before(
        function () use ($app) {
            return true;
        }
    );

    /*
     * htmlを返却
     */
    $app->get('/',
        function () use ($app) {
            $app->view->render('index', 'index');

            return null; // 明示的にnullを返却するか、returnを書かない
        }
    );

    /*
     * Jsonを返却
     */
    $app->get('/json',
        function () use ($app) {
            return ['data' => ['hoge' => 1]]; // 結果を['data' => [array]]のフォーマットで返却
        }
    );

    /*
     * エラーを返却
     */
    $app->get('/error',
        function () use ($app) {
            throw new AppException('想定内エラー', 403); // 想定内エラーはAppExceptionを用いる
        }
    );

    /*
     * レスポンスの共通処理など
     * error以外で実行される
     */
    $app->after(
        function () use ($app) {
            $val = $app->getReturnedValue();
            // true: htmlの返却のため何もしない
            if (empty($val)) {
                return null;
            }

            // false: jsonのテンプレートに各ハンドラーの結果を代入して返却
            $response = new Response();
            $response->setContentType('application/json');
            $response->setContent(json_encode(['result' => true, 'data' => $val['data']]));

            return $response->send();
        }
    );

    /*
     * レスポンス返却後の共通処理
     * error以外で実行される
     */
    $app->finish(
        function () use ($app) {
            return null;
        }
    );

    /*
     * 定義済みエラーのハンドリング
     */
    $app->error(
        function (Exception $e) use ($app) {
            switch (get_class($e)) {
                case 'AppException':
                    return $e->response();
                default:
                    throw $e;
            }
        }
    );

    $app->handle();
} catch (Throwable $e) {
    // 想定外エラーのハンドリング
    $e = new AppException($e->getMessage(), 500, $e);

    return $e->response()->send();
}

/**
 * 例外クラス
 * Class AppException.
 */
class AppException extends Exception
{
    public function __construct(String $message, Int $code, Exception $previous = null)
    {
        parent::__construct($message, $code,  $previous);
    }

    public function response() : Response
    {
        global $config;
        $body = ['result' => false, 'message' => $this->getMessage()];

        // true: エラーのスタックトレースを返却
        if ($config->debug->error === true) {
            $body['stack'] = [];
            $body['stack'][] = $this->getTrace();
            $exception = $this;
            while (($exception = $exception->getPrevious()) !== null) {
                $body['stack'][] = $exception->getTrace();
            }
        }

        $response = new Response(json_encode($body), $this->getCode());

        return $response->setContentType('application/json');
    }
}
