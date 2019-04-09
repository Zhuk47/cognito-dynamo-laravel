<?php

namespace App\Http\Controllers;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Marshaler;
use Aws\Sdk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PodPoint\LaravelCognitoAuth\CognitoClient;

class HomeController extends Controller
{
    protected $dynamoDB;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->dynamoDB = new DynamoDbClient([
            'credentials' => [
                'key' => config('cognito.credentials.key'),
                'secret' => config('cognito.credentials.secret'),
            ],
            'region' => 'us-west-2',
            'version' => 'latest'
        ]);

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        /** @var CognitoClient $client */
        $client = app()[CognitoClient::class];

        $userEmail = Auth::user()->email;
        $user = $client->getUser($userEmail);
        $pool = $client->getPoolMetadata();
        $users = $client->getPoolUsers();

        $notes = $this->dynamoDB->query([
            'TableName' => 'notes',
            'IndexName' => 'userId',
            'KeyConditionExpression' => 'userId = :userId',
            'ExpressionAttributeValues' => [
                ':userId' => [
                    'S' => $user->get('Username'),
                ],
            ],
        ])->get('Items');

        return view('home', compact('user', 'pool', 'users', 'notes'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function create(Request $request)
    {
        /** @var CognitoClient $client */
        $client = app()[CognitoClient::class];

        $userEmail = Auth::user()->email;
        $user = $client->getUser($userEmail);
        $marshaler = new Marshaler();

        $tableName = 'notes';
        $userId = $user->get('Username');
        $noteId = (string)random_int(100000, 999999);
        $note = $request->note;

        $item = $marshaler->marshalJson('
            {
                "userId": "' . $userId . '",
                "noteId": ' . $noteId . ',
                "note": "' . $note . '"
            }
        ');

        $params = [
            'TableName' => $tableName,
            'Item' => $item,
        ];

        $this->dynamoDB->putItem($params);

        return redirect(route('home'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete(Request $request)
    {
        $marshaler = new Marshaler();

        $tableName = 'notes';
        $noteId = $request->noteId;

        $key = $marshaler->marshalJson('
            {
                "noteId": ' . $noteId . '
            }
        ');

        $params = [
            'TableName' => $tableName,
            'Key' => $key,
        ];

        $this->dynamoDB->deleteItem($params);

        return redirect(route('home'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request)
    {
        $marshaler = new Marshaler();

        $tableName = 'notes';
        $noteId = $request->noteId;
        $note = $request->note;

        $key = $marshaler->marshalJson('
            {
                "noteId": ' . $noteId . '
            }
        ');

        $eav = $marshaler->marshalJson('
            {
                ":note": "' . $note . '"
            }
        ');

        $params = [
            'TableName' => $tableName,
            'Key' => $key,
            'UpdateExpression' =>
                'set note = :note',
            'ExpressionAttributeValues'=> $eav,
            'ReturnValues' => 'UPDATED_NEW',
        ];

        $this->dynamoDB->updateItem($params);

        return redirect(route('home'));
    }
}
