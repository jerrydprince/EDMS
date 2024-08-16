<x-mail::message>
<h2>{{$data['subject']}}</h2>
{!! $data['body'] !!} <br/>
<div style="margin:0 auto; text-align:center">
    <h1 style="padding-top:20px; padding-bottom:20px"></h1>
</div>

Thanks,<br>
{{ config('app.name')}}
</x-mail::message>


