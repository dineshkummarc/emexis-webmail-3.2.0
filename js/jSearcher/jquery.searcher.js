/**************************************************
	Class:  Pretty Search
	Author: Egor Hmelyoff (hmelyoff@gmail.com)
	TODO:
		— allow safari to use own input[type=search]
		— find and return object if exist
	
**************************************************/
/*global document, jQuery */


(function( $ ) {

  $.fn.searcher = function( options ){
		return this.each(function(){
		  new jSearcher( this, options );
		});
  };

  var OPTIONS = {
    className: "j-searcher",
    template: tmpl(
      '<div <%=params.id%> class="<%=className%>">' +
  			'<div class="<%=className%>_left"><i></i></div>' +
  			'<div class="<%=className%>_container">' +
  			  '<div class="<%=className%>_right"><i></i></div>' + 
  			  '<div class="<%=className%>_container">' +
  					'<span class="<%=className%>_placeholder"><%=params.placeholder%></span>' +
  					'<input type="text" name="<%=params.name%>" value="<%=params.value%>" class="<%=className%>_input <%=params.className%>" results="<%=params.results%>" autocomplete="<%=params.autocomplete%>" />' +
  					'<span class="<%=className%>_spinner"></span>' +
  					'<span class="<%=className%>_clear"></span>' +
  				'</div>'+
  			'</div>'+
      '</div>'
      
    )
  };

  var jSearcher = Class.extend({
    init: function( node, options ){
  		this.is = {
  			input: false
  		};

  		this.domNode = $(node);
  		
  		this.options = $.extend(true, {}, OPTIONS, options ? options : {});
		  this.options.params = {
		    width: this.domNode.width()-13
		  };
  		
  		if( this.domNode.is("input") ){
  		  // create node by template
  		  $.extend(this.options.params, {
  		    // attrs
          id: this.domNode.attr("id") ? ('id="' + this.domNode.attr("id") + '"') : '',
  		    className: this.domNode.attr("class"),
  		    placeholder: this.domNode.attr("placeholder") ? this.domNode.attr("placeholder") : '',
          results: this.domNode.attr("results") ? this.domNode.attr("results") : 0,
          autocomplete: this.domNode.attr("autocomplete") ? this.domNode.attr("autocomplete") : 'on',
  		    incremental: this.domNode.attr("incremental") ? this.domNode.attr("incremental") : 'no',
          onsearch: this.domNode.attr("onsearch") ? this.domNode.attr("onsearch") : function(){},
          
          // form
          name: this.domNode.attr("name") ? this.domNode.attr("name") : 'q',
          value: this.domNode.attr("value") ? thid.domNode.attr("value") : ''
  		  });
  		  
  		  var template = $(this.options.template( this.options ));
        template.find( "." + this.options.className +"_container input" ).css({ outlineWidth: 0 });
  		  this.domNode.replaceWith(template);
  		  this.domNode = template;
  		  
  		} else if(this.domNode.is("." + this.options.className)) {
  		  // init already templated node
  		  var input = this.domNode.find("." + this.options.className + "_input");
  		  
  		  $.extend(this.options.params, {
  		    placeholder: this.domNode.find("." + this.options.className + "_placeholder").html(),
  		    incremental: input.attr("incremental") ? input.attr("incremental") : 'no',
          onsearch: input.attr("onsearch") ? input.attr("onsearch") : function(){},
          value: input.attr("value") ? input.attr("value") : ''
  		  });
  		  
        input.css({ outlineWidth: 0 });

  		} else {
  		  // wtf? stop here
  		  return false;
  		}

      // continue init by data
  		this.domNode.data("SearchHandler", this);

      if(!this.options.params.value && this.options.params.placeholder) {
        this.placeholder(true);
      }
       
      if(this.options.params.value) {
        this.clear(true);
      }
      
      this._events();
  		
    },
    
    _events: function(){
      var self = this;
      var input = this.domNode.find("." + this.options.className + "_input");

      this.domNode
        .mousedown(function(){
          self.placeholder(false);
          self.domNode.addDependClass("focus");

          if(!self.is.input) return false;
          else self.is.input = false;
        })
        .mouseup(function(){
          input.focus();
        });
      
      input
        .focus(function(){
          self.domNode.addDependClass("focus");
          self.placeholder(false);
        })
        .blur(function(){
          self.domNode.removeDependClass("focus");
          if(!input.val()) self.placeholder(true);
        })
        .keyup(function(){
          if(input.val())
            self.clear(true);
          else
            self.clear(false);

          self.value = input.val();
          if(self.options.params.incremental == 'yes') {
            self.onsearch();
          }
        })
        .mousedown(function(){
          self.is.input = true;
        })
        .change(function(){
          self.value = input.val();
          if(self.options.params.incremental == 'yes') {
            self.onsearch();
          }
        })
        .parents("form").submit(function(){
          self.onsubmit();
        });

      this.domNode.find("." + this.options.className + "_clear")
        .mousedown(function(){
          $(this).addDependClass("down", "_");
          input.focus().select();
          return false;
        })
        .click(function(){
          self.value = input.val("").focus().val();
          $(this).removeDependClass("down", "_").hide();
          if(self.options.params.incremental == 'yes') {
            self.onsearch();
          }
        });

      this.onsearch = function(){
        if($.isFunction(this.options.params.onsearch)) {
          this.options.params.onsearch.apply(self);
        } else {
          eval(this.options.params.onsearch);
        }
      };

      this.events();

    },

    events: function(){ },

    onsearch: function(){ },

    onsubmit: function(){
      this.onsearch();
    },
    
    placeholder: function( b ){
      var p = this.domNode.find("." + this.options.className + "_placeholder");
      if( b ) p.show();
      else    p.hide();
    },

    clear: function( b ){
      var p = this.domNode.find("." + this.options.className + "_clear");
      if( b ) p.show();
      else    p.hide();
    },

    loading: function( b ){
      if( b )
        this.domNode.addDependClass("loading");
      else
        this.domNode.removeDependClass("loading");
    }
    
    
  });

})( jQuery );
