<?php

namespace Tasdan\ColumnSearchable;

class SearchableScript
{
    public static function render()
    {
        return '
            function getSearchParams()
            {
                var searchParams = {};
                $(".searchable-input").each(function () {
                    console.log($(this).val());
                    if ($(this).val()) {
                        searchParams[$(this).attr("id")] = $(this).val();
                    }
                });
                return decodeURIComponent($.param(searchParams));
            }
            
            function search()
            {
                var baseUrl = "'. url()->current() .'";
                window.location = baseUrl + "?" + getSearchParams();
            }
        ';
    }
}
