<x-mail::message>
# Email Verification

Please use the code below to verify your Email <br/>
<div style="margin:0 auto; text-align:center">
    <h1 style="padding-top:20px; padding-bottom:20px">{{$code}}</h1>
</div>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
