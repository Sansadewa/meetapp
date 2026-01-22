@extends('layout.index')
@section('title') Blank @endsection

@section('css')
<style>
</style>
@endsection

@section('content')
<!-- ============================================================== -->
<!-- Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<div class="row page-titles">
    <div class="col-md-5 col-12 align-self-center">
        <h3 class="text-themecolor mb-0">Blank Page</h3>
    </div>
    <div class="col-md-7 col-12 align-self-center d-none d-md-flex justify-content-end">
        <ol class="breadcrumb mb-0 p-0 bg-transparent">
            <li class="breadcrumb-item"><a href="javascript:void(0)">Home</a></li>
            <li class="breadcrumb-item active">Blank Page</li>
        </ol>
    </div>
</div>
<!-- ============================================================== -->
<!-- End Bread crumb and right sidebar toggle -->
<!-- ============================================================== -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Blank Page</h4>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Nostrum a totam corrupti. 
                    Quam cumque hic fuga, modi dolore assumenda in, vel deserunt ducimus dolorum quis blanditiis velit eum ea officia? 
                    Lorem ipsum dolor sit amet consectetur adipisicing elit. 
                    Aspernatur non ut deserunt voluptatum voluptate illo similique magnam, 
                    est assumenda dicta dolores labore at ex quae consequuntur tenetur reiciendis quo quos?
                </p>            
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function(){
    console.log('ready');
})
</script>
@endsection