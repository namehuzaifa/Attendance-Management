<?php


use Illuminate\Support\Str;

/**
 * Asset URL stamped with the file's modification time, so a changed
 * stylesheet reaches browsers without anyone remembering to bump a ?v= by
 * hand — the stale-CSS trap that hid the icon sizing rules.
 */
function versionedAsset(string $path): string
{
    $full = public_path($path);

    return asset($path) . (is_file($full) ? '?v=' . filemtime($full) : '');
}


function navActive($route_name)
{
    return (in_array(request()->route()->getName(), $route_name) ? "active" : '');
}


function slugGenerator($value, $model ,$column_name)
{
    $slug = Str::slug($value);

    if ($model::where($column_name, $slug)->exists()) {
        $slug = $slug.'-'.date('dmyhis');
    }

    return $slug;
}


function getMediaType($url,$type)
{
    $imgExts = array("gif", "jpg", "jpeg", "png", "tiff", "tif");
    $vidExts = ["mp4", "3gp", "ogg"];
    $urlExt = pathinfo($url, PATHINFO_EXTENSION);
    return in_array($urlExt, $type == "img" ? $imgExts : $vidExts);
}
