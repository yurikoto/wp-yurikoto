$(document).ready(function(){
    $.get("https://v1.yurikoto.com/sentence?encode=text", function(data, status){
        if(status === "success"){
            $(".yurikoto-sentence").text(data);
        }
    });
});
