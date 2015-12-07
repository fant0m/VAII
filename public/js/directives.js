var app = angular.module('directives', []);

app.directive('activePage', function($location) {
    return {
        restrict: 'A',
        link: function(scope, element, attributes) {
            scope.$on('$routeChangeSuccess', function() {
                if ($location.path() === attributes['activePage']) {
                    element.addClass('active');
                } else {
                    element.removeClass('active');
                }
            });
        }
    };
});

app.directive('tooltip', function() {
    return function(scope, element, attrs) {
        element.tooltip();
        element.on('click', function(){
            $(this).tooltip('hide');
        });
    };
});

app.directive('popover', function($rootScope, $timeout, User) {
    return function(scope, element, attrs) {
        if (attrs.content) {
            element.popover({html: true, content: attrs.content});
        } else {
            if ($rootScope.logged) {
                if (user.notifications.length > 0) {
                    var content = "<ul class='nav nav-list nav-stacked'>";
                    for (var i = 0; i < user.notifications.length; i++) {
                        content += '<li class="nav-item"><a href="' + user.notifications[i].link + '">' + user.notifications[i].text + '</a></li>';
                    }
                    content += '</ul>';
                    element.popover({html: true, content: content});
                } else {
                    element.popover({html: true, content: '<p>Žiadne nové oznámenia nie sú k dispozícii.</p>'});
                }
            }
        }

        attrs.$observe('count', function(value){
            element.popover('dispose');
            if ($rootScope.logged) {
                if (user.notifications.length > 0) {
                    var content = "<ul class='nav nav-list nav-stacked'>";
                    for (var i = 0; i < user.notifications.length; i++) {
                        content += '<li class="nav-item"><a href="' + user.notifications[i].link + '">' + user.notifications[i].text + '</a></li>';
                    }
                    content += '</ul>';
                    element.popover({html: true, content: content});
                } else {
                    element.popover({html: true, content: '<p>Žiadne nové oznámenia nie sú k dispozícii.</p>'});
                }
            }
        });

        var state = 0;
        element.bind('click', function() {
            if (state == 1) {
                element.popover('hide');
                state = 0;
                User.updateNotifications();
            } else {
                element.popover('show');
                var move = $('.popover').last().width() / 100 * 30;
                $('.popover').last().css({left: '-' + move + 'px'});
                state = 1;
            }
        }).bind('blur', function() {
            $timeout(function() {
                element.popover('hide');
                state = 0;
                User.updateNotifications();
            }, 100);
        });

        $(window).scroll(function() {
            var move = $('.popover').last().width() / 100 * 30;
            $('.popover').last().css({left: '-' + move + 'px'});
        });
    };
});
