<div {{ $attributes->merge(['class' => 'alert alert-' . $type, "style" => "background-color:red;"], true) }}>{{ $message ?? "alert" }}</div>