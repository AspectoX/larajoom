@extends('admin_tpl::index')

@section('content')
    <section id="content" class="content">
        <jdoc:include type="message" />
        @position('customtop')
        <div class="row">
            <div class="col-md-12">
                <main>
                    <h1>{{ __('lj_dashboard') }}</h1>
                    <p>{{ __('lj_welcome') }}</p>
                </main>
            </div>
        </div>
    </section>
@endsection
