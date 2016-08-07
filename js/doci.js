(function(scope){
    $ = jQuery;

    /**
     *
     */
    var Class = function(def, extend)
    {
        if(typeof extend != 'undefined'){
            extend = extend.prototype;

            for(var i in extend){
                if(typeof def[i] === 'undefined'){
                    def[i] = extend[i];
                }
            }
        }

        var constructor = function()
        {
            if (typeof this.init !== 'undefined') {
                this.init.apply(this, arguments);
            }
        }

        constructor.prototype = def;

        return constructor;
    }

    /**
     *
     */
    var BaseClass = Class(
    {
        init : function()
        {
            var that = this;

            $.each(this, function(method, value){
                if(method.substring(0,6) == 'before' && that.isFunction(value) && method != 'before'){
                    var eventName = method[6].toLowerCase()+method.substring(7);
                    that.before(eventName, value.bind(that));
                    return null;
                }

                if(method.substring(0,2) == 'on' && that.isFunction(value) && method != 'on'){
                    var eventName = method[2].toLowerCase()+method.substring(3);
                    that.on(eventName, value.bind(that));
                    return null;
                }

                if(method.substring(0,5) == 'after' && that.isFunction(value) && method != 'after'){
                    var eventName = method[5].toLowerCase()+method.substring(6);
                    that.after(eventName, value.bind(that));
                }
            });

            this.prepare.apply(this, arguments);
        },

        prepare : function(){},

        // data bus
        _data : {},
        setData : function(name, value, silient)
        {
            silient = silient || false;

            var oldData = this._data[name];

            this._data[name] = value;

            if(!silient){
                // Ustawienie danych, nie powoduje, wywołania zdarzeń.
                if(this.isUndefined(oldData)){
                    this.triggerInitedData(name);
                }else{
                    if(oldData != value){
                        this.triggerChangeData(name);
                    }
                }
            }
        },

        triggerChangeData : function(name)
        {
            this.trigger('data-changed');
            this.trigger('data-'+name+'-changed');

            return this;
        },

        triggerInitedData : function(name)
        {
            this.trigger('data-changed');
            this.trigger('data-'+name+'-inited');

            return this;
        },

        getData : function(name)
        {
            return this._data[name];
        },

        isDefinedData : function(name)
        {
            return this.getData(name) !== 'undefined';
        },

        // utils
        _generateId : function()
        {
            if (this.isUndefined(this._localId)) {
                this._localId = 0;
            }

            return ++this._localId;
        },

        isUndefined : function(param)
        {
            return typeof param === 'undefined';
        },

        $ : function(element)
        {
            return $(element);
        },

        pattern : function(pattern, values)
        {
            $.each(values, function(name, value){
                pattern = pattern.replace('$'+name, value);
            });

            return pattern;
        },

        findNode : function(id)
        {
            var nodeContener = this.getData('nodeContener');

            var node = nodeContener.find(id);

            if (node == null) {
                throw("Brak takiego noda.");
            }

            return node;
        },

        isDefined: function(param)
        {
            return typeof param !== 'undefined';
        },

        isNull : function(param)
        {
            return param === null;
        },

        isNotNull : function(param)
        {
            return param !== null;
        },

        type : function(variable)
        {
            return typeof variable;
        },

        extend : function(target, object)
        {
            //$.extend(target, object);
            return this;
        },

        isString : function(variable)
        {
            return this.type(variable) === 'string';
        },

        isFunction : function(variable)
        {
            return this.type(variable) === 'function';
        },

        dragging : function(jq, inConfig)
        {
            var config = {
                start : function(){},
                drag : function(){},
                stop : function(){},
            };

            $.extend(config, inConfig);

            // Czy dragging jest aktywny.
            var active = false;

            // Aktualne polozenie x i y;
            var clientX;
            var clientY;

            // Polozenie kursora w poprzednim cyklu.
            var prClientX;
            var prClientY;

            // poczatkowe polozenie
            var startX;
            var startY;

            var checking = function(){
                if (active) {
                    if(prClientX != clientX || prClientY != clientY){
                        config.drag.call(jq,
                        {
                            x : clientX,
                            y : clientY,
                        },
                        {
                            x : clientX - startX,
                            y : clientY - startY,
                        });

                        prClientX = clientX;
                        prClientY = clientY;
                    }

                    setTimeout(checking, 100);
                }
            }

            jq.mousedown(function(event){
                startX = clientX;
                startY = clientY;

                config.start.call(jq, {
                    x : clientX,
                    y : clientY,
                });

                active = true;
                checking();
            });

            $(document).mouseup(function () {
                active = false;
            });

            // Aktualizujemy polozenie cursora
            $(document).mousemove(function(event) {
                clientX = parseInt(event.clientX);
                clientY = parseInt(event.clientY);
            });
        },

        // event bus
        _eventsBefore : {},
        _events : {},
        _eventsAfter : {},

        _initEvent : function(eventName)
        {
            this._eventsBefore[eventName] = this._eventsBefore[eventName] || [];
            this._events[eventName] = this._events[eventName] || [];
            this._eventsAfter[eventName] = this._eventsAfter[eventName] || [];
        },

        before : function(eventName, callback)
        {
            var that = this;

            if (this.isString(eventName)) {
                eventName = [eventName];
            }

            $.each(eventName, function(i, name){
                that._initEvent(name);
                that._eventsBefore[name].push(callback);
            });

            return this;
        },

        on : function(eventName, callback)
        {
            var that = this;

            if (this.isString(eventName)) {
                eventName = [eventName];
            }

            $.each(eventName, function(i, name){
                that._initEvent(name);
                that._events[name].push(callback);
            });

            return this;
        },

        after : function(eventName, callback)
        {
            var that = this;

            if (this.isString(eventName)) {
                eventName = [eventName];
            }

            $.each(eventName, function(i, name){
                that._initEvent(name);
                that._eventsAfter[name].push(callback);
            });

            return this;
        },

        _queue : [],
        _eventInfo : {
            inExecute : false,
        },

        trigger : function(eventName)
        {
            var that = this;

            this._initEvent(eventName);

            var allEvents = [];

            if (this._eventsBefore[eventName].length > 0) {
                allEvents = allEvents.concat(this._eventsBefore[eventName]);
            }

            if (this._events[eventName].length > 0) {
                allEvents = allEvents.concat(this._events[eventName]);
            }

            if (this._eventsAfter[eventName].length > 0) {
                allEvents = allEvents.concat(this._eventsAfter[eventName]);
            }

            var args = Array.prototype.slice.call(arguments);
            args = args.splice(1);

            $.each(allEvents, function(i, callback){
                var caller = function(){
                    callback.apply(that, args);
                }

                that._queue.push(caller);
            });

            this._execute();
        },

        _execute : function()
        {
            if (this._eventInfo.inExecute) {
                return;
            }

            this._eventInfo.inExecute = true;

            while(this._queue.length > 0){
                var caller = this._queue.shift();
                caller();
            }

            this._eventInfo.inExecute = false;
        },
    });

    /**
     *
     */
    var View = Class(
    {
        _views : null,
        _layout : null,

        prepare : function()
        {
            if(this.isNotNull(this._layout)){
                this._layout = $(this._layout);
            }

            this.ready();
        },

        ready : function(){},

        view : function(name)
        {
            if(this.isNull(this._views)){
                this._views = {};
            }

            if (this.isDefined(this._views[name])) {
                return this._views[name];
            }

            if (this.isNull(this._layout)) {
                throw("You must implement _layout");
            }

            var view = this._layout.find('.doci-'+name);

            view = view[0];

            if (this.isUndefined(view)) {
                if(this._layout.hasClass('doci-'+name)){
                    this._views[name] = this._layout;
                    return this._layout;
                }

                layout = this._layout;
                return null
            }else{
                this._views[name] = $(view);
                return $(view);
            }
        },

        append : function(to)
        {
            to.append(this._layout);
        },

        renderIn : function(to)
        {
            to.children().detach();
            to.append(this._layout);
        },
    }
    , BaseClass);

    /**
     *
     */
    var Header = Class(
    {
        _layout : "\n\
            <div class='doci-headerFrame'>\n\
                Juborm\n\
            </div>\n\
        ",

        getHeight : function()
        {
            return this.view('headerFrame').outerHeight();
        },

    }, View);

    var Breadcrumbs = Class(
    {
        _layout : "\n\
            <div class='doci-breadcrumbsFrame'>\n\
                <div class='doci-breadcrumbs'>\n\
                </div>\n\
            </div>\n\
        ",

        onLoadPage : function(id)
        {
            var that = this;
            var node = this.findNode(id);
            var path = node.getPath();

            this.view('breadcrumbs').html("");

            $.each(path, function(i, node) {
                if(node.isRoot()){
                    return null;
                }

                var name = $('<div>').addClass('doci-breadcrumbsName');
                name.html(node.getName());

                name.click(function(){

                    that.trigger('loadPage', node.getId());
                });

                var sep = $('<div>').addClass('doci-breadcrumbsSep');
                sep.html('/');

                that.view('breadcrumbs').append(sep);
                that.view('breadcrumbs').append(name);
            });

            that.view('breadcrumbs').append($('<div>').addClass('doci-cleaner'));

        }

    }, View);

    /**
     *
     */
    var Content = Class(
    {
        _layout : "\n\
            <div class='doci-contentFrame'>\n\
                <div class='doci-content'>\n\
                </div>\n\
            </div>\n\
        ",

        onLoadPage : function(id)
        {
            this.loadPage(id);
        },

        loadPage : function(id)
        {
            var nodeContener = this.getData('nodeContener');
            var node = nodeContener.find(id);

            this.view('content').html(node.getContent());

            return this;
        },

    }, View);

    /**
     *
     */
    var Panel = Class(
    {
        _layout : "\n\
            <div class='doci-panelFrame'>\n\
                <div class='doci-panelButtonsFrame'>\n\
                    <div class='doci-panelButtons'>\n\
                        <div class='doci-panelButtonsIco doci-panelButtonsMinimalize'></div>\n\
                        \n\
                        <div class='doci-panelButtonsIco doci-panelButtonsLike'></div>\n\
                        <div class='doci-panelButtonsIco doci-panelButtonsLiked'></div>\n\
                        <div class='doci-panelButtonsIco doci-panelButtonsLikedList'></div>\n\
                        <div class='doci-panelButtonsIco doci-panelButtonsContentTable'></div>\n\
                        <div class='doci-panelButtonsIco doci-panelButtonsSearch'></div>\n\
                    </div>\n\
                </div>\n\
                <div class='doci-panelContentFrame'>\n\
                    <div class='doci-panelContent'>\n\
                    </div>\n\
                </div>\n\
                <div class='doci-panelResizeFrame'>\n\
                    <div class='doci-panelContentResize'>\n\
                    </div>\n\
                </div>\n\
            </div>\n\
        ",

        // zawartosci spisu tresci
        _contentTable : null,
        // lista ulubionych stron
        _likedList : null,
        _search : null,

        _lastSelectedLi : null,

        ready : function()
        {
            var that = this;
            var config = this.getData('config');

            // inicujeje podstawowe divy
            this._contentTable = $('<div class="doci-contentTable">');
            this._likedList = $('<div class="doci-likedList">');

            this._attachEvents();

            // ustawiam szerokosc panelu zgodnie z konfiguracja
            this.setWith(config.panelWith);
        },

        afterPagesLoaded : function()
        {
            // jak wczytaja sie strony to tworze content table oraz liste
            // ulubionych
            this.renderContentTable();
            this.renderLikedList();
            this.renderSearch();
        },

        afterLoadPage : function(id)
        {
            this.select(id);

            var likedList = this.getData('likedList');

            if(likedList.isLiked(id)){
                this.afterLike();
            }else{
                this.afterNotLike();
            }
        },

        onMinimalizePanel : function()
        {
            this.setWith(0);
        },

        afterLike : function()
        {
            this.view('panelButtonsLike').hide();
            this.view('panelButtonsLiked').show();

            this.renderLikedList();
        },

        afterNotLike : function()
        {
            this.view('panelButtonsLike').show();
            this.view('panelButtonsLiked').hide();

            this.renderLikedList();
        },

        onShowContentTable : function()
        {
            this.show('contentTable');
        },

        onShowLikedList : function()
        {
            this.show('likedList');
        },

        onShowSearch : function()
        {
            this.show('search');
        },

        onAdjustTheDimensions : function()
        {
            var panelFrame = this.view('panelFrame');

            var headearHeightOutter = this.getData('header').getHeight();
            panelFrame.css('top', headearHeightOutter);

            var footHeightOutter = this.getData('foot').getHeight();
            panelFrame.css('height', 'calc(100% - '+(headearHeightOutter+footHeightOutter)+'px)');
        },

        onShowPanel : function()
        {
            var config = this.getData('config');
            this.setWith(config.panelWith);
        },

        onSearch : function(key)
        {
            var that = this;
            this.trigger('showSearch');

            var nodeContener = this.getData('nodeContener');
            var searchResultSet = this.view('searchResultSet');
            searchResultSet.html('');

            nodeContener.findByContent(key, function(results){
                $.each(results, function(i, result) {
                    var searchResult = $("<div>").addClass('doci-searchResult');
                    searchResult.click(function(){
                        that.trigger('loadPage', result.id);
                    });

                    var searchResultName = $("<div>").addClass('doci-searchResultName');
                    var searchResultPhrase = $("<div>").addClass('doci-searchResultPhrase');

                    searchResultName.html(result.name);

                    var re = new RegExp(key, 'g');
                    result.phrase = result.phrase.replace(re, '<span class="doci-searchResultSearchKey">'+key+'</span>');

                    searchResultPhrase.html(result.phrase);

                    searchResult.append(searchResultName);
                    searchResult.append(searchResultPhrase);

                    searchResultSet.append(searchResult);

                });
            });

        },

        onRollDown : function(id, after)
        {
            var that = this;
            var node = this.findNode(id);
            var path = node.getPath();

            $.each(path, function(i, node) {
                var ico = node.getIco();
                var ul = node.getUl();

                ul.show();
                ico.removeClass('doci-contentTableIcoRollDown');
                ico.addClass('doci-contentTableIcoRollUp');

                // ul.slideDown('slow', function(){
                //     ico.removeClass('doci-contentTableIcoRollDown');
                //     ico.addClass('doci-contentTableIcoRollUp');
                // });
            });

            return this;
        },

        onRollUp : function(id, after)
        {
            var that = this;
            var node = this.findNode(id);

            var ico = node.getIco();
            var ul = node.getUl();

            ul.hide();
            ico.removeClass('doci-contentTableIcoRollUp');
            ico.addClass('doci-contentTableIcoRollDown');

            // ul.slideUp('slow', function(){
            //     ico.removeClass('doci-contentTableIcoRollUp');
            //     ico.addClass('doci-contentTableIcoRollDown');
            // });

            return this;
        },

        _attachEvents : function()
        {
            var that = this;

            this.view('panelButtonsLike').click(function(){
                var selectedNode = that.getData('selectedNode');
                that.trigger('like', selectedNode);
            });

            this.view('panelButtonsLiked').click(function(){
                var selectedNode = that.getData('selectedNode');
                that.trigger('notLike', selectedNode);
            });

            this.view('panelButtonsLikedList').click(function(){
                that.trigger('showLikedList');
            });

            // showContentTable
            this.view('panelButtonsContentTable').click(function(){
                that.trigger('showContentTable');
            });

            // minimalizePanel
            this.view('panelButtonsMinimalize').click(function(){
                that.trigger('minimalizePanel');
            });

            // minimalizePanel
            this.view('panelButtonsSearch').click(function(){
                that.trigger('showSearch');
            });
        },

        onLikePresentPage : function()
        {
            var selectedNode = this.getData('selectedNode');
            this.trigger('like', selectedNode);

        },

        onNotLikePresentPage : function()
        {
            var selectedNode = this.getData('selectedNode');
            this.trigger('notLike', selectedNode);
        },

        renderLikedList : function()
        {
            var that = this;
            var likedList = this.getData('likedList');
            var all = likedList.getAll();

            this._likedList.html('');

            var ul = $('<ul>');
            $.each(all, function(i, id){
                var node = that.findNode(id);
                var li = $('<li>').addClass('doci-likedListNode');

                var name = $('<div>').addClass('doci-likedListName');
                name.html(node.getName());

                name.click(function(){
                    that.trigger('loadPage', id);
                });

                li.append(name);

                ul.append(li);
            });

            that._likedList.append(ul);
        },

        renderContentTable : function(queue)
        {
            var that = this;

            // usuwam wszystkie poprzednie nody
            this._contentTable.children().remove();

            // renderuje nody na nowo
            var rootNode = this.getData('rootNode');
            var nodeContener = this.getData('nodeContener');

            function renderNode(parentLi, node)
            {
                var ul = $('<ul>');

                node.setUl(ul);
                node.setLi(parentLi);
                node.setIco(parentLi.find('.doci-contentTableIco'));

                $.each(node.getChildren(), function(i, childNode){
                    var li = $('<li>');
                    var id = childNode.getId();

                    li.addClass('doci-contentTableNode');

                    var ico = $('<div>').addClass('doci-contentTableIco') ;

                    if(childNode.hasChildren()){
                        ico.addClass('doci-contentTableIcoRollDown');
                    }else{
                        ico.addClass('doci-contentTableIcoParagraph');
                    }

                    var name = $('<div>')
                        .html(childNode.getName())
                        .addClass('doci-contentTableName')
                    ;

                    name.click(function(){
                        that.trigger('loadPage', id);
                    });

                    li.append(ico);
                    li.append(name);

                    ul.append(li);

                    var childUl = renderNode(li, childNode);

                    ico.click(function(event){
                        event.stopPropagation();

                        if(childUl.is(':visible')){
                            that.trigger('rollUp', id);
                        }else{
                            that.trigger('rollDown', id);
                        }
                    });

                    li.click(function(){
                        that.trigger('selectNode', id);
                        //that.select(id);
                    });

                });

                ul.hide();

                parentLi.after(ul);

                return ul;
            }

            var rootDiv = $('<div>');
            this._contentTable.append(rootDiv);
            renderNode(rootDiv, rootNode);
        },

        renderSearch : function()
        {
            var that = this;
            if (!this.isNull(this._search)) {
                // wyszukiwarka jest juz wyrenderowana
                return;
            }

            var layout = "\n\
            <div class='doci-search'>\n\
                <div class='doci-searchForm'>\n\
                    <input class='doci-input doci-searchFormKey'>\n\
                    <button class='doci-button doci-searchFormSearchButton'>Szukaj</button>\n\
                </div>\n\
                <div class='doci-searchResultSet'>\n\
                </div>\n\
            </div>\n\
            ";

            layout = $(layout);

            this._search = layout;

            var key = layout.find('.doci-searchFormKey');

            layout.find('.doci-searchFormSearchButton').click(function(){
                that.trigger('search', key.val());
            });
        },

        show : function(content)
        {
            this.trigger('showPanel');

            this.view('panelContent').children().detach();

            switch (content) {
                case 'contentTable':
                    this.view('panelContent').append(this._contentTable);
                    break;
                case 'likedList':
                    this.view('panelContent').append(this._likedList);
                    break;
                case 'search':
                    this.view('panelContent').append(this._search);
                    this._search.find('.doci-searchFormKey').focus();
                    break;
            }
        },

        minimalizePanel : function()
        {
            this.setWith(0);
            return this;
        },

        select: function(id, after)
        {
            after = after || function(){};

            var node = this.findNode(id);
            var li = node.getLi();
            var ul = node.getUl();

            if(this._lastSelectedLi != null){
                this._lastSelectedLi.removeClass('doci-contentTableNodeSelected');
            }

            this._lastSelectedLi = li;

            li.addClass('doci-contentTableNodeSelected');

            if (!ul.is(':visible')) {
                this.trigger('rollDown', node.getId());
            }
        },

        getWith : function()
        {
            return this.view('panelFrame').outerWidth();
        },

        setWith : function(width)
        {
            var minWith = this.view('panelButtonsFrame').outerWidth() + this.view('panelResizeFrame').outerWidth();

            if(width < 100){
                width = minWith + 1;
            }

            this.view('panelFrame').css('width', width);

            return this;
        },

    }, View);

    /**
     *
     */
    var Foot = Class(
    {
        _layout : "\n\
            <div class='doci-footFrame'>\n\
            </div>\n\
        ",

        ready : function()
        {
        },

        onAdjustTheDimensions : function()
        {
            var footHeightOutter = this.getHeight();
            this.view('footFrame').css('top', this.pattern('calc(100% - $vpx)', {v : footHeightOutter}))
        },

        getHeight : function()
        {
            return this.view('footFrame').outerHeight();
        },

    }, View);

    /**
     *
     */
    var Link = Class(
    {
        _id : "",
        _delayCheck : false,

        prepare : function(config)
        {
            this._initObserver();
        },

        onLoadPage : function(id)
        {
            this.setId(id);
        },

        _initObserver : function()
        {
            var that = this;

            if ("onhashchange" in scope) {
                scope.onhashchange = this.check.bind(this);
            } else {
                scope.setInterval(function () {
                    that.check.bind(that);
                }, 100);
            }
        },

        check : function()
        {
            if(this._delayCheck){
                this._delayCheck = false;
                return;
            }

            var idFromHash = this.getIdFromHash();

            if (idFromHash === this.getId()) {
                return;
            }

            // inicujeje zdarzenie wczytania strony
            this.trigger('loadPage', idFromHash);
        },

        setId : function(id, preventEvent)
        {
            preventEvent = preventEvent || false;

            if(this._id == id){
                // link sie nie zmienil
                return;
            }

            this._id = id;

            if(preventEvent === false){
                this.trigger('link-changed');
            }

            this._updateLink();

            return this;
        },

        getIdFromHash : function()
        {
            var idFromHash = scope.location.hash.substring(1);

            if(idFromHash === ""){
                idFromHash = null;
            }

            return idFromHash;
        },

        _updateLink : function()
        {
            if(this.getIdFromHash() === this.getId()){
                return;
            }

            this._delayCheck = true;
            scope.location.hash = this._id;

        },

        getId : function()
        {
            return this._id;
        },

    }, BaseClass);

    /**
     *
     */
    var Keyboard = Class(
    {
        _pressKeys : [],
        _registeredShortcuts : [],
        _keyboardShortcuts : {},

        prepare : function()
        {
            this._parseShortcutsConfig();
            this._initKeyEvent();
        },

        _parseShortcutsConfig : function()
        {
            var that = this;
            var config = this.getData('config');

            $.each(config.keyboardShortcuts, function(eventName, shortcut){
                that._registeredShortcuts.push(shortcut);
                that._keyboardShortcuts[shortcut] = eventName;
            });
        },

        _initKeyEvent : function()
        {
            var that = this;

            $(document).keydown(function(event){
                var key = that._getKeyFromKeyCode(event.keyCode);

                if(that._pressKeys.indexOf(key) == -1){
                    // dany przycisk nie jest nacisniety
                    that._pressKeys.push(key);

                    // po nacisnieciu wywoluje zdarzenie
                    that._callEvent();
                }

                if(that._registeredShortcuts.indexOf(that.getPressKeys()) !== -1){
                    event.preventDefault();
                }
            });

            $(document).keyup(function(event){
                var key = that._getKeyFromKeyCode(event.keyCode);

                var indexOf = that._pressKeys.indexOf(key);

                if (indexOf != -1) {
                    that._pressKeys.splice(indexOf);
                }

            });

            $(document).focusout(function(){
                // jak okno traci focus to usuwam wszystkie skroty
                that._pressKeys = [];
            });
        },

        _getKeyFromKeyCode : function(keyCode)
        {
            var controlKey = {
                16 : 'Shift',
                17 : 'Control',
                18 : 'Alt',
                27 : 'Esc',
                // arrow
                37 : 'ArrowLeft',
                38 : 'ArrowUp',
                39 : 'ArrowRight',
                40 : 'ArrowDown',
            }

            var key = controlKey[keyCode];

            if (this.isUndefined(key)) {
                key = String.fromCharCode(keyCode);
            }

            return key;
        },

        _callEvent : function()
        {
            var that = this;

            var pressShortcut = this.getPressKeys();
            var eventName = this._keyboardShortcuts[pressShortcut];

            if(!this.isUndefined(eventName)){
                this.trigger(eventName);
            }
        },

        getPressKeys : function()
        {
            var that = this;

            var keys = null;

            $.each(this._pressKeys, function(i, key){
                if (that.isNull(keys)) {
                    keys = key;
                }else{
                    keys += '-'+key;
                }
            });

            return keys;
        },
    }, BaseClass);

    /**
     *
     */
    var Node = Class(
    {
        init : function()
        {
            this._id = null;
            this._name = null;
            this._children = [];
            this._ico = null;

            this._content = null;

            this._parent = null;
            this._ul = null;
            this._li = null;
        },

        setRoot : function()
        {
            this._id = 0;
            return this;
        },

        isRoot : function()
        {
            return this._id == 0;
        },

        getPath : function()
        {
            var node = this;
            var path = [];

            do{
                path.push(node);
            }while(node = node.getParent());

            path.reverse();

            return path;
        },

        getContent : function(after)
        {
            return this._content;

            if (!Utils.isNull(this._content)) {
                // wczytano juz content wiec zwracam
                after.call(this, this._content);
            }else{
                // nie ma kontentu
                var contentSource = this.static.contentSource;

                if (!Utils.isNull(contentSource)) {
                    // ale jest callback wiec wykonuje
                    var that = this;
                    var callback = function(content)
                    {
                        that._content = content;
                        after(content);
                    }

                    contentSource.call(that, that.getId(), callback);
                }else{

                    after.call(this, this._content);
                }
            }
        },

        setContent : function(content)
        {
            if (this.isString(content)) {
                content = $(content);
            }

            this._content = content;
            return this;
        },

        setUl: function(ul)
        {
            this._ul = ul;
            return this;
        },

        setLi: function(li)
        {
            this._li = li;
            return this;
        },

        getUl : function()
        {
            return this._ul;
        },

        setIco: function(ico)
        {
            this._ico = ico;
            return this;
        },

        getIco : function()
        {
            return this._ico;
        },

        getLi : function()
        {
            return this._li;
        },

        getId : function()
        {
            return this._id;
        },

        setId : function(id)
        {
            this._id = id;
            return this;
        },

        getName : function()
        {
            return this._name;
        },

        setName : function(name)
        {
            //someText = someText.replace(/(\r\n|\n|\r)/gm,"");
            this._name = name.replace(/(\r\n|\n|\r)/gm,"");
            return this;
        },

        getParent : function()
        {
            return this._parent;
        },

        setParent : function(parent)
        {
            this._parent = parent;
            return this;
        },

        addChildren : function(node)
        {
            //this.static.addNodeToIndex(node);
            node.setParent(this);
            this._children.push(node);
        },

        getChildren : function()
        {
            return this._children;
        },

        hasChildren : function()
        {
            var children = this.getChildren();

            if(children.length > 0){
                return true;
            }else{
                return false;
            }
        }

    }, BaseClass);

    /**
     *
     */
    var NodeContener = Class(
    {
        _indexed : {},

        init : function()
        {
        },

        add : function(node)
        {
            this._indexed[node.getId()] = node;
            return this;
        },

        findByContent : function(key, loadResult)
        {
            var result = [];

            $.each(this._indexed, function(id, node){
                var content = node.getContent().html();
                var matches = content.match(key);

                // var re = /\.*?([^\.]*?(Paweł).*?)\./g;
                var re = new RegExp("\\.*?([^\\.]*?("+key+")\\.*?)", 'g');
                var str = content;
                var m;

                while ((m = re.exec(str)) !== null) {
                    if (m.index === re.lastIndex) {
                        re.lastIndex++;
                    }

                    result.push({
                        id : node.getId(),
                        name : node.getName(),
                        phrase : m[1].replace(/<\/?[^>]+(>|$)/g, ""),
                    });
                }
            });

            loadResult(result);
        },

        find : function(id)
        {
            if(this.isUndefined(this._indexed[id])){
                return null;
            }

            return this._indexed[id];
        },

        first : function()
        {
            var first = null;
            $.each(this._indexed, function(id){
                first = id;
            });

            return first;
        },
    }, BaseClass);

    var LikedList = Class(
    {
        prepare: function()
        {
            var liked = localStorage.getItem('dociLiked');

            if(this.isNull(liked)){
                liked = [];
            }else{
                liked = JSON.parse(liked);
            }

            this._liked = liked;
        },

        getAll : function()
        {
            return this._liked;
        },

        isLiked : function(id)
        {
            id = parseInt(id);
            return this._liked.indexOf(id) != -1;
        },

        onLike : function(id)
        {
            this.add(id);
        },

        onNotLike : function(id)
        {
            this.remove(id);
        },

        remove : function(id)
        {
            var index = this._liked.indexOf(id);

            if (index > -1) {
                this._liked.splice(index, 1);
            }

            this.save();
        },

        add : function(id)
        {
            id = parseInt(id);

            if(this.isLiked(id)){
                return;
            }

            this._liked.push(id);
            this.save();
        },

        save : function()
        {
            var string = JSON.stringify(this._liked);
            window.localStorage.setItem('dociLiked', string);
        },

    }, BaseClass);

    /**
     *
     */
    var Doci = Class(
    {
        _layout : "\n\
            <div class='doci'>\n\
                <div class='doci-frame'>\n\
                </div>\n\
            </div>\n\
        ",

        prepare : function(config)
        {
            this.setData('config', config);
            this.setData('doci', this);

            this._layout = $(this._layout);

            this.ready();
        },

        ready : function()
        {
            var that = this;
            var config = this.getData('config');

            // wczytanie elementow interfejsu

            //_initBreadcrumbs
            var breadcrumbs = new Breadcrumbs();
            this.setData('breadcrumbs', breadcrumbs);
            breadcrumbs.append(this.view('frame'));

            // initContent
            var content = new Content();
            this.setData('content', content);
            content.append(this.view('frame'));

            //_initPanel
            var panel = new Panel();
            this.setData('panel', panel);
            panel.append($('body'));


            //_initHeader
            var header = new Header();
            this.setData('header', header);
            header.append($('body'));

            //_initFoot
            var foot = new Foot();
            this.setData('foot', foot);
            foot.append($('body'));

            //_initLink
            var link = new Link();
            this.setData('link', link);

            //_initLikedList
            var likedList = new LikedList();
            this.setData('likedList', likedList);

            // initNodes - cos co wymaga bardziej zlozonej inicjacji, t
            this._initNodes();

            if (config.keyboardSupport) {
                var keybord = new Keyboard();
                this.setData('keybord', keybord);
            }

            // umieszczenie dokumentacji w strukturze dokumentu
            var target = config.target;
            this.append(target);

            this.trigger('adjustTheDimensions');
            this.trigger('showContentTable');
        },

        onAdjustTheDimensions : function()
        {
            var headearHeightOutter = this.getData('header').getHeight();
            var panelWithOutter = this.getData('panel').getWith();

            this.view('frame').css('margin-top', headearHeightOutter);
            this.view('frame').css('margin-left', panelWithOutter);
        },

        onPagesLoaded : function()
        {
            this._loadFirst();
        },

        beforeLoadPage : function(id)
        {
            // eksportuje informacje o wybranej stronie aby inne elementy mogly
            // sie do niej odwolac
            this.setData('selectedNode', id);
        },

        afterShowPanel : function()
        {
            this.onAdjustTheDimensions();
        },

        afterMinimalizePanel : function()
        {
            this.onAdjustTheDimensions();
        },

        _loadFirst : function()
        {
            var link = this.getData('link');
            var nodeContener = this.getData('nodeContener');

            var initPage = link.getIdFromHash();

            if(this.isNull(initPage)){
                initPage = nodeContener.first();
            }

            this.trigger('loadPage', initPage);
        },


        _initNodes : function()
        {
            var that = this;
            var config = this.getData('config');

            if (this.isNull(config.menuSource)) {
                // uzytkownik nie podal zrodla danych, wiec zrodlem beda
                // elementy targetu.
                config.menuSource = this._loadNodesFromDocument.bind(this);
            }

            var loadNodes = function(rootNodeOrDef)
            {
                if(that.isDefined(rootNodeOrDef.rootNode) && that.isDefined(rootNodeOrDef.nodeContener)){
                    that.setData('rootNode', rootNodeOrDef.rootNode);
                    that.setData('nodeContener', rootNodeOrDef.nodeContener);
                }else{

                }

                that.trigger('pagesLoaded');
            }

            config.menuSource.call(this, loadNodes);
        },

        _loadNodesFromDocument : function(loadNodes)
        {
            var config = this.getData('config');
            var target = config.target;

            // w pierwszym kroku tworze strukture paragrafow
            var rootNode = new Node();
            rootNode.setRoot();

            var nodeContener = new NodeContener();

            this._findParagraphs(config.target, rootNode, nodeContener);

            //Node.static.addNodeToIndex(rootNode);

            // odbinam wszystkie paragrafy z dokumentu
            $('.paragraph').detach();

            loadNodes({
                rootNode : rootNode,
                nodeContener : nodeContener,
            });
        },

        _findParagraphs : function(where, parentNode, nodeContener)
        {
            var that = this;

            where.children('.paragraph').each(function(i, paragraph){
                paragraph = $(paragraph);

                var node = new Node();
                node.setContent(paragraph);

                var id = paragraph.attr('id');
                if (that.isUndefined(id)) {
                    // nie podano id paragrafu, wiec generuje
                    id = that._generateId();
                }

                node.setId(id);

                var name = paragraph.children('.head').html();
                if (name) {
                    node.setName(name);
                }

                that._findParagraphs(paragraph, node, nodeContener);

                nodeContener.add(node);
                parentNode.addChildren(node);
            });
        }
    }
    , View);

    /**
     *
     */
    $.fn.doci = function(inConfig)
    {
        var config = {
            panelWith : 300,
            target : $(this),

            menuSource : null,

            // keybord
            keyboardSupport : true,
            keyboardShortcuts : {
                showSearch : 'Control-F',
                showContentTable : 'Control-B',
                showLikedList : 'Control-J',
                likePresentPage : 'Control-L',
                notLikePresentPage : 'Control-K',
                minimalizePanel : 'Esc',
                // arrows
                ArrowUp : 'ArrowUp',
                ArrowDown : 'ArrowDown',
                ArrowLeft : 'ArrowLeft',
                ArrowRight : 'ArrowRight',

            },
        };

        $.extend(config, inConfig);

        doci = new Doci(config);
    }

})(window);

