var app = angular.module('services', []);

app.factory('cache', function($templateCache, $location, $route) {

    function remove(template) {
        $templateCache.remove(template);
    };

    function removeReload(template) {
        $templateCache.remove(template);
        $route.reload();
    }

    function goHome() {
        $templateCache.remove('partials/home');
        $location.path('/');
        $route.reload();
    };

    function removeAllGoHome() {
        $templateCache.removeAll();
        $location.path('/');
    };

    function removeAllReload() {
        $templateCache.removeAll();
        $route.reload();
    }

    return {
        remove: remove,
        goHome: goHome,
        removeReload: removeReload,
        removeAllGoHome: removeAllGoHome,
        removeAllReload: removeAllReload
    }
});

app.factory('notify', function() {
    function alert(type, text, interval) {
        interval = interval || 2000;
        $('#notify #alert').empty().append('<div class="alert alert-' + type + ' alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span><span class="sr-only">Zatvoriť</span></button>'+ text + '</div>').animate({bottom: '0%'}, 'fast');
        if (interval != 0) {
            setTimeout(function() {
                $('#notify #alert').animate({'bottom': '-20%'}, function() { $(this).children().remove(); });
            }, interval);
        }
    };

    return {
        alert: alert
    };
});

app.factory('User', function($http, $route, notify, cache, $rootScope) {
    var logged = state;
    var self = this;

    function login(data) {
        $http.post('/login', {email: data.email, password: data.password}).success(function(data) {
            if (data.errors) {
                notify.alert('danger', data.errors.join('<br>'), 3000);
            } else if (data.success) {
                $rootScope.logged = state = self.logged = 1;
                $rootScope.user = user = data.user;
                notify.alert('success', data.success);
                cache.removeAllGoHome();
            } else {
                notify.alert('danger', 'Chyba pri odosielaní formulára!<br>Nastala neznáma chyba.');
            }
        });
    }

    function logout() {
        $http.post('/logout').success(function() {
            notify.alert('success', 'Boli ste úspešne odhlásený.');
            $rootScope.logged = state = self.logged = 0;
            $rootScope.user = user = 0;
            cache.removeAllGoHome();
        });
    }

    function changePassword(data) {
        $http.post('/change-password', {old_password: data.old_password, password: data.password, password_confirmation: data.password_confirmation}).success(function(data){
            if (data.errors) {
                notify.alert('danger', data.errors.join('<br>'), 3000);
            } else if (data.success) {
                notify.alert('success', data.success);
                cache.remove($route.current.templateUrl);
            } else {
                notify.alert('danger', 'Chyba pri odosielaní formulára!<br>Nastala neznáma chyba.');
            }
        });
    }

    function update(data) {
        $http.post('/change-profile', {profile: data.profile
        }).success(function(data){
           notify.alert('success', data.success);
           cache.remove($route.current.templateUrl);
        }).error(function(){
           notify.toast('Nastala chyba');
        });
    }

    function register(data) {
        $http.post('/register', {email: data.email, nick: data.nick, password: data.password, password_confirmation: data.password_confirmation}).success(function(data) {
            if (data.errors) {
                notify.alert('danger', data.errors.join('<br>'), 3000);
            } else if (data.success) {
                $rootScope.logged = state = self.logged = 1;
                $rootScope.user = user = data.user;
                notify.alert('success', data.success);
                cache.goHome();
            } else {
                notify.alert('danger', 'Chyba pri odosielaní formulára!<br>Nastala neznáma chyba.');
            }
        });
    }

    function addPost(data) {
        $http.post('/post', {title: data.title, text: data.text, type: data.type, privacy: data.privacy}).success(function(data) {
            if (data.errors) {
                notify.alert('danger', data.errors.join('<br>'), 3000);
            } else if (data.success) {
                notify.alert('success', data.success);
                cache.goHome();
            } else {
                notify.alert('danger', 'Chyba pri odosielaní formulára!<br>Nastala neznáma chyba.');
            }
        });
    }

    function follow(id, type) {
        $http.post('/follow', {id: id, type: type}).success(function(data) {
            if (data.error) {
                notify.alert('danger', data.error, 3000);
            } else if (data.success) {
                notify.alert('success', data.success);
                cache.remove('/partials/user/' + $route.current.pathParams.id);
                cache.removeReload('/partials/user/' + user.id);
            } else {
                notify.alert('danger', 'Nastala neznáma chyba!');
            }
        });
    }

    function checkNotifications() {
        $http.post('/notifications').success(function(data) {
            $rootScope.user.notifications = user.notifications = data;
        });
    }

    function updateNotifications() {
        $http.post('/update-notifications').success(function(data) {
            $rootScope.user.notifications = user.notifications = data;
        });
    }

    return {
        login: login,
        logout: logout,
        update: update,
        register: register,
        changePassword: changePassword,
        addPost: addPost,
        follow: follow,
        checkNotifications,
        updateNotifications
    }
});
