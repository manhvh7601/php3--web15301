@extends('welcome')
@section('title', 'Đăng nhập')
@section('content')
    <div class="container">
        <div class="col-10 offset-1">
            @if (session()->get('error'))
                <div class="alert alert-danger">
                    {{session()->get('error')}}
                </div>
            @endif
            <form action="{{route('auth.login')}}" method="post">
                @csrf
                <div class="row">
                    <label for="">Email</label>
                    <input type="email" name="email" class="form-control" value="{{old('email')}}">
                </div>
                <div class="row">
                    <label for="">Password</label>
                    <input type="password" name="password" class="form-control">
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>
@endsection