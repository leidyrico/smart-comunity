@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Importar Apartamentos desde CSV') }}</div>

                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('deudas.import.process') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group row">
                            <label for="file" class="col-md-4 col-form-label text-md-right">{{ __('Archivo CSV') }}</label>

                            <div class="col-md-6">
                                <input id="file" type="file" class="form-control @error('file') is-invalid @enderror" name="file" required accept=".csv,.txt">

                                @error('file')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Importar') }}
                                </button>
                                
                                <a href="{{ route('deudas.template') }}" class="btn btn-secondary ml-2">
                                    {{ __('Descargar Plantilla') }}
                                </a>
                                
                                <a href="{{ route('deudas.index') }}" class="btn btn-outline-secondary ml-2">
                                    {{ __('Cancelar') }}
                                </a>
                            </div>
                        </div>
                    </form>
                    
                    <div class="mt-4">
                        <h5>Instrucciones:</h5>
                        <ul>
                            <li>El archivo debe ser un CSV con las columnas: Numero, Propietario, Telefono, Email</li>
                            <li>La primera fila debe contener los encabezados</li>
                            <li>Use la plantilla proporcionada como referencia</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection