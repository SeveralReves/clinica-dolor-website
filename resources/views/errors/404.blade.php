@extends('layouts.default')

@section('content')

@section('title', 'Página no encontrada')

<section class="error">
    <div class="error__container container">
        <img src="{{ asset('images/404.png') }}" width="400" loading="lazy" alt="Camión de mudanza" class="error__image">
        <h1 class="error__title">
            Ups, Página no encontrada
        </h1>
        <p class="error__description">
           Lo sentimos, parece que la página que intentas buscar no existe
        </p>
        <div class="error__buttons">
            <a href="/" class="error__button button__primary">
                Regresar al Inicio
            </a>
        </div>
    </div>
</section>
@stop