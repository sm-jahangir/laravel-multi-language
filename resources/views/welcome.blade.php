<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Collapsible sidebar using Bootstrap 4</title>
    <!-- Bootstrap CSS CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

</head>
<body>
    <div class="container">
        <div class="dropdown">
            {{-- <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Language ({{ Str::ucfirst(\Illuminate\Support\Facades\Session::get('locale') ?? env('DEFAULT_LANGUAGE')) }})
            </button> --}}
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Language ({{ Str::ucfirst(\Illuminate\Support\Facades\Session::get('locale') ?? env('DEFAULT_LANGUAGE')) }})
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                @foreach (\App\Models\Language::all() as $language)
                    <a class="dropdown-item" href="{{ route('language.change') }}"
                        onclick="event.preventDefault();
                           document.getElementById('{{ $language->name }}').submit()">
                        <img width="25" height="auto" src="{{ asset('images/lang/' . $language->image) }}"
                            alt="" />
                        {{ $language->name }}</a>
                    <form id="{{ $language->name }}" class="d-none" action="{{ route('language.change') }}"
                        method="POST">
                        @csrf
                        <input type="hidden" name="code" value="{{ $language->code }}">
                    </form>
                @endforeach
            </div>
        </div>

        <p>@translate(Come to your home)</p>
            <p>@translate(Never Forget Me)</p>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>

</body>

</html>
