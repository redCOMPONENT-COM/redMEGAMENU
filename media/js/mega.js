(function(a){if(typeof define==="function"&&define.amd){define(["jquery"],a)}else{if(jQuery&&!jQuery.fn.hoverIntent){a(jQuery)}}})(function(f){var b={interval:100,sensitivity:6,timeout:0};var d=0;var h,g;var a=function(i){h=i.pageX;g=i.pageY};var e=function(l,j,k,i){if(Math.sqrt((k.pX-h)*(k.pX-h)+(k.pY-g)*(k.pY-g))<i.sensitivity){j.off("mousemove.hoverIntent"+k.namespace,a);delete k.timeoutId;k.isActive=true;delete k.pX;delete k.pY;return i.over.apply(j[0],[l])}else{k.pX=h;k.pY=g;k.timeoutId=setTimeout(function(){e(l,j,k,i)},i.interval)}};var c=function(l,j,k,i){delete j.data("hoverIntent")[k.id];return i.apply(j[0],[l])};f.fn.hoverIntent=function(m,n,i){var l=d++;var k=f.extend({},b);if(f.isPlainObject(m)){k=f.extend(k,m)}else{if(f.isFunction(n)){k=f.extend(k,{over:m,out:n,selector:i})}else{k=f.extend(k,{over:m,out:m,selector:n})}}var j=function(t){var r=f.extend({},t);var p=f(this);var o=p.data("hoverIntent");if(!o){p.data("hoverIntent",(o={}))}var s=o[l];if(!s){o[l]=s={id:l}}if(s.timeoutId){s.timeoutId=clearTimeout(s.timeoutId)}var q=s.namespace=".hoverIntent"+l;if(t.type==="mouseenter"){if(s.isActive){return}s.pX=r.pageX;s.pY=r.pageY;p.on("mousemove.hoverIntent"+q,a);s.timeoutId=setTimeout(function(){e(r,p,s,k)},k.interval)}else{if(!s.isActive){return}p.off("mousemove.hoverIntent"+q,a);s.timeoutId=setTimeout(function(){c(r,p,s,k.out)},k.timeout)}};return this.on({"mouseenter.hoverIntent":j,"mouseleave.hoverIntent":j},k.selector)}});(function(a){jQuery.fn.shopbMegaMenu=function(n){var u;a.extend(u={showSpeed:300,hideSpeed:300,trigger:"hover",showDelay:0,hideDelay:0,effect:"fade",align:"left",responsive:true,animation:"none",indentChildren:true,indicatorFirstLevel:"+",indicatorSecondLevel:"+",minWidth:768},n);var w=a(this);var x=a(w).children(".shopbMegaMenu-menu");var y=a(x).find("li");var z;var A=2000;var q=200;a(x).children("li").children("a").each(function(){if(a(this).siblings(".dropdown, .megamenu")["length"]>0){a(this).append("<span class='indicator'>"+u.indicatorFirstLevel+"</span>")}});a(x).find(".dropdown").children("li").children("a").each(function(){if(a(this).siblings(".dropdown")["length"]>0){a(this).append("<span class='indicator'>"+u.indicatorSecondLevel+"</span>")}});a(x).find(".megamenu").find("li ul li a").each(function(){if(a(this).siblings(".dropdown")["length"]>0){a(this).append("<span class='indicator'>"+u.indicatorSecondLevel+"</span>")}});if(u.align=="right"){a(x).addClass("shopbMegaMenu-right")}if(u.indentChildren){a(x).addClass("shopbMegaMenu-indented")}if(u.responsive){a(w).addClass("shopbMegaMenu-responsive").prepend("<a href='javascript:void(0)' class='showhide'><em></em><em></em><em></em></a>");z=a(w).children(".showhide")}function e(h,j){h=h||"hover";if(h!="click"){j=a(this)}if(u.effect=="fade"){j.children(".dropdown, .megamenu").delay(u.showDelay).fadeIn(u.showSpeed).addClass(u.animation)}else{j.children(".dropdown, .megamenu").delay(u.showDelay).slideDown(u.showSpeed).addClass(u.animation)}}function v(h,j){h=h||"hover";if(h!="click"){j=a(this)}if(u.effect=="fade"){j.children(".dropdown, .megamenu").delay(u.hideDelay).fadeOut(u.hideSpeed).removeClass(u.animation)}else{j.children(".dropdown, .megamenu").delay(u.hideDelay).slideUp(u.hideSpeed).removeClass(u.animation)}}function g(){a(x).find(".dropdown, .megamenu").hide(0);if(navigator.userAgent.match(/Mobi/i)||window.navigator.msMaxTouchPoints>0||u.trigger=="click"){a(".shopbMegaMenu-menu > li > a, .shopbMegaMenu-menu ul.dropdown li a").bind("click touchstart",function(h){h.stopPropagation();h.preventDefault();a(this).parent("li").siblings("li").find(".dropdown, .megamenu").stop(true,true).fadeOut(300);if(a(this).siblings(".dropdown, .megamenu").css("display")=="none"){e("click",a(this).parent("li"));return false}else{v("click",a(this).parent("li"))}window.location.href=a(this)["attr"]("href")});a(document).bind("click.menu touchstart.menu",function(h){if(a(h.target)["closest"](".shopbMegaMenu")["length"]==0){a(".shopbMegaMenu-menu").find(".dropdown, .megamenu").fadeOut(300)}})}else{a(y).hoverIntent(e,v)}}function C(){a(x).find(".dropdown, .megamenu").hide(0);a(x).find(".indicator").each(function(){if(a(this).parent("a").siblings(".dropdown, .megamenu")["length"]>0){a(this).bind("click",function(h){if(a(this).parent().prop("tagName")=="A"){h.preventDefault()}if(a(this).parent("a").siblings(".dropdown, .megamenu").css("display")=="none"){a(this).parent("a").siblings(".dropdown, .megamenu").delay(u.showDelay).slideDown(u.showSpeed);a(this).parent("a").parent("li").siblings("li").find(".dropdown, .megamenu").slideUp(u.hideSpeed)}else{a(this).parent("a").siblings(".dropdown, .megamenu").slideUp(u.hideSpeed)}})}})}function i(){var j=a(x).children("li").children(".dropdown");if(a(window).innerWidth()>u.minWidth){var k=a(w).outerWidth(true);for(var h=0;h<j.length;h++){if(a(j[h]).parent("li").position.left+a(j[h]).outerWidth()>k){a(j[h]).css("right",0)}else{if(k==a(j[h]).outerWidth()||(k-a(j[h]).outerWidth())<20){a(j[h]).css("left",0)}if(a(j[h]).parent("li").position.left+a(j[h]).outerWidth()<k){a(j[h]).css("right","auto")}}}}}function B(){a(x).hide(0);a(z).show(0).click(function(){if(a(x).css("display")=="none"){a(x).slideDown(u.showSpeed)}else{a(x).slideUp(u.hideSpeed).find(".dropdown, .megamenu").hide(u.hideSpeed)}})}function c(){a(x).show(0);a(z).hide(0)}function f(){a(w).find("li, a").unbind();a(document).unbind("click.menu touchstart.menu")}function l(){function h(m){var o=a(m).find(".shopbMegaMenu-tabs-nav > li");var p=a(m).find(".shopbMegaMenu-tabs-content");a(o).bind("click touchstart",function(r){r.stopPropagation();r.preventDefault();a(o).removeClass("active");a(this).addClass("active");a(p).hide(0);a(p[a(this).index()]).show(0)})}if(a(x).find(".shopbMegaMenu-tabs")["length"]>0){var k=a(x).find(".shopbMegaMenu-tabs");for(var j=0;j<k.length;j++){h(k[j])}}}function d(){return window.innerWidth||document.documentElement.clientWidth||document.body.clientWidth}function b(){i();var k=a(".megamenu");k.css({left:"0px",width:"100%"});var j=-(document.body.clientWidth-k.width())/2;k.css({left:j+"px",width:(document.body.clientWidth)+"px"});if(d()<=u.minWidth&&A>u.minWidth){f();if(u.responsive){B();C()}else{g()}}if(d()>u.minWidth&&q<=u.minWidth){f();c();g()}A=d();q=d();l();if(/MSIE (d+.d+);/["test"](navigator.userAgent)&&d()<u.minWidth){var h=new Number(RegExp.$1);if(h==8){a(z).hide(0);a(x).show(0);f();g()}}a(".shopbMegaMenu").on("click",".accordion",function(){var m=a(this).parent().parent().find(".collapse");if(m.hasClass("in")){m.css({overflow:"hidden"})}}).on("shown.bs.collapse",".accordion",function(){a(this).parent().parent().find(".collapse").css({overflow:"visible"})})}b();a(window).resize(function(){b();i()})}}(jQuery));