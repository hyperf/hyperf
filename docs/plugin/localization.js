window.HyperfLocalization = {
    loadPlugin: function(hook, vm) {
        hook.beforeEach(function(to, from, next) {
            var shortMatches = ['en'],
                // zh-cn is the default so is not present
                fullMatches = ['zh-tw', 'zh-hk'],
                locale = (navigator.languages
                ? navigator.languages[0]
                : (navigator.language || navigator.userLanguage)).toLowerCase();

            for (var i = 0; i < shortMatches.length; i++) {
                if (locale.substr(0, 2) === shortMatches[i] && vm.route.path === '/') {
                    window.location.hash = '/' + shortMatches[i] + '/';
                }
            }

            for (i = 0; i < fullMatches.length; i++) {
                if (locale === fullMatches[i] && vm.route.path === '/') {
                    window.location.hash = '/' + fullMatches[i] + '/';
                }
            }
        })
    }
};