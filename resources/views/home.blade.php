@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Dashboard</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        You are logged in!
                        <div>
                            <h1>Secure page</h1>
                            <p>Welcome <strong>{{$user->get('Username')}}</strong> ! You are successfully authenticated.
                                Some <em>secret</em> information about this user pool:</p>

                            <h2>Metadata</h2>
                            <p><b>Id: </b>{{$pool['Id']}}</p>
                            <p><b>Name: </b>{{$pool['Name']}}</p>
                            <p><b>CreationDate: </b>{{$pool['CreationDate']}}</p>

                            <h2>Users</h2>
                            <ul>
                                @foreach($users as $user)
                                    <li>{{$user['Username'] . '    Email: '. $user['Attributes'][3]['Value']}}</li>
                                @endforeach
                            </ul>
                        </div>
                        <div>
                            {{--                            {{Cognito}}--}}
                        </div>
                    </div>
                    <div>
                        <h2>Notes</h2>
                        <h5>(from DynamoDB)</h5>
                        <ul>
                            @foreach($notes as $note)
                                <li>
                                    <div class="row">
                                        <div class="col-8">
                                            <form method="post" action="{{route('update')}}">
                                                @csrf
                                                <div class="row form-group">
                                                    <div class="col-10">
                                                        <input hidden type="text" class="form-control" name="noteId"
                                                               value="{{$note['noteId']['N']}}"/>
                                                        <input type="text" class="form-control" name="note"
                                                               value="{{$note['note']['S']}}"/>
                                                    </div>
                                                    <div class="col-2">
                                                        <input type="submit" class="btn btn-info" value="Update"/>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-2">
                                            <form method="post" action="{{route('delete')}}">
                                                @csrf
                                                <div class="form-group">
                                                    <input hidden type="text" class="form-control" name="noteId"
                                                           value="{{$note['noteId']['N']}}"/>
                                                    <input type="submit" class="btn btn-danger delete-user" value="X"/>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <form method="post" action="{{ route('create') }}">
                            <div class="form-group">
                                @csrf
                                <label for="name">Note:</label>
                                <input type="text" class="form-control" name="note"/>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Item</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
