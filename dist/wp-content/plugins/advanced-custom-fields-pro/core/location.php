<?php 

class acf_location {

	/*
	*  __construct
	*
	*  Initialize filters, action, variables and includes
	*
	*  @type	function
	*  @date	23/06/12
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function __construct() {
		
		// Post
		add_filter( 'acf/location/rule_match/post_type',		array($this, 'rule_match_post_type'), 10, 3 );
		add_filter( 'acf/location/rule_match/post',				array($this, 'rule_match_post'), 10, 3 );
		add_filter( 'acf/location/rule_match/post_category',	array($this, 'rule_match_post_taxonomy'), 10, 3 );
		add_filter( 'acf/location/rule_match/post_format',		array($this, 'rule_match_post_format'), 10, 3 );
		add_filter( 'acf/location/rule_match/post_status',		array($this, 'rule_match_post_status'), 10, 3 );
		add_filter( 'acf/location/rule_match/post_taxonomy',	array($this, 'rule_match_post_taxonomy'), 10, 3 );
		
		
		// Page
		add_filter( 'acf/location/rule_match/page',				array($this, 'rule_match_post'), 10, 3 );
		add_filter( 'acf/location/rule_match/page_type',		array($this, 'rule_match_page_type'), 10, 3 );
		add_filter( 'acf/location/rule_match/page_parent',		array($this, 'rule_match_page_parent'), 10, 3 );
		add_filter( 'acf/location/rule_match/page_template',	array($this, 'rule_match_page_template'), 10, 3 );
		
		
		// User
		add_filter( 'acf/location/rule_match/current_user',		array($this, 'rule_match_current_user'), 10, 3 );
		add_filter( 'acf/location/rule_match/current_user_role',	array($this, 'rule_match_current_user_role'), 10, 3 );
		add_filter( 'acf/location/rule_match/user_form',		array($this, 'rule_match_user_form'), 10, 3 );
		add_filter( 'acf/location/rule_match/user_role',		array($this, 'rule_match_user_role'), 10, 3 );
		
		
		// Form
		add_filter( 'acf/location/rule_match/taxonomy',			array($this, 'rule_match_taxonomy'), 10, 3 );
		add_filter( 'acf/location/rule_match/attachment',		array($this, 'rule_match_attachment'), 10, 3 );
		add_filter( 'acf/location/rule_match/comment',			array($this, 'rule_match_comment'), 10, 3 );
		add_filter( 'acf/location/rule_match/widget',			array($this, 'rule_match_widget'), 10, 3 );
		
	}
		
	
	/*
	*  rule_match_post_type
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match_post_type( $match, $rule, $options ) {
		
		// vars
		$post_type = $options['post_type'];
		
		
		// find post type for current post
		if( !$post_type ) {
			
			if( !$options['post_id'] ) {
				
				return false;
				
			}
			
			$post_type = get_post_type( $options['post_id'] );
		}
		
		
		// compare
        if( $rule['operator'] == "==" ) {
        	
        	$match = ( $post_type === $rule['value'] );
        	
        } elseif( $rule['operator'] == "!=" ) {
        	
        	$match = ( $post_type !== $rule['value'] );
        	
        }
        
		
		// return
		return $match;
	}
	
	
	/*
	*  rule_match_current_user
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match_current_user( $match, $rule, $options ) {
		
		// logged in
		if( $rule['value'] == 'logged_in' ) {
			
			if( $rule['operator'] == "==" ) {
				
				$match = is_user_logged_in();
					
			} elseif( $rule['operator'] == "!=" ) {
				
				$match = !is_user_logged_in();
					
			}
			
			return $match;
			
		}
		
		
		// front end
		if( $rule['value'] == 'viewing_front' ) {
			
			if( $rule['operator'] == "==" ) {
				
				$match = !is_admin();
					
			} elseif( $rule['operator'] == "!=" ) {
				
				$match = is_admin();
					
			}
			
			return $match;
			
		}
		
		
		// back end
		if( $rule['value'] == 'viewing_back' ) {
			
			if( $rule['operator'] == "==" ) {
				
				$match = is_admin();
					
			} elseif( $rule['operator'] == "!=" ) {
				
				$match = !is_admin();
					
			}
			
			return $match;
			
		}
		
		
        // return
        return $match;
        
    }
    
    
    /*
	*  rule_match_current_user_role
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match_current_user_role( $match, $rule, $options ) {
		
		// bail early if not logged in
		if( !is_user_logged_in() ) {
			
			return false;
			
		}
		
		
		// vars
		$user = wp_get_current_user();
		
		
		// compare
        if( $rule['operator'] == "==" ) {
        	
			if( $rule['value'] == 'super_admin' ) {
				
				$match = is_super_admin( $user->ID );
				
			} else {
				
				$match = in_array( $rule['value'], $user->roles );
				
			}
			
		} elseif( $rule['operator'] == "!=" ) {
			
			if( $rule['value'] == 'super_admin' ) {
				
				$match = !is_super_admin( $user->ID );
				
			} else {
				
				$match = ( ! in_array( $rule['value'], $user->roles ) );
				
			}
			
		}
        
        
        // return
        return $match;
        
    }
    
    
	/*
	*  rule_match_post
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match_post( $match, $rule, $options ) {
		
		// vars
		$post_id = $options['post_id'];
		
		
		// validation
		if( !$post_id ) {
		
			return false;
			
		}
		
		
		// translate $rule['value']
		// - this variable will hold the original post_id, but $options['post_id'] will hold the translated version
		//if( function_exists('icl_object_id') )
		//{
		//	$rule['value'] = icl_object_id( $rule['value'], $options['post_type'], true );
		//}
		
		
		// compare
        if( $rule['operator'] == "==") {
        	
        	$match = ( $options['post_id'] == $rule['value'] );
        
        } elseif( $rule['operator'] == "!=") {
        	
        	$match = ( $options['post_id'] != $rule['value'] );
        
        }
        
        
        // return
        return $match;

	}
	
	
	/*
	*  rule_match_post_taxonomy
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match_post_taxonomy( $match, $rule, $options ) {
		
		// validate
		if( !$options['post_id'] ) {
		
			return false;
			
		}
		
		
		// vars
		$terms = $options['post_taxonomy'];
		
			
		// get term data
		$data = acf_decode_taxonomy_term( $rule['value'] );
		$field = is_numeric( $data['term'] ) ? 'id' : 'slug';
		$term = get_term_by( $field, $data['term'], $data['taxonomy'] );
		
		
		// validate term
		if( empty($term) ) {
			
			return false;
						
		}
		
		
		// post type
		if( !$options['post_type'] ) {
		
			$options['post_type'] = get_post_type( $options['post_id'] );
			
		}
		
		
		// get terms
		if( !$options['ajax'] ) {
		
			$terms = wp_get_post_terms( $options['post_id'], $term->taxonomy, array('fields' => 'ids') );
			
		}
		
		
		// If no terms, this is a new post and should be treated as if it has the "Uncategorized" (1) category ticked
		if( empty($terms) ) {
			
			if( is_object_in_taxonomy($options['post_type'], 'category') ) {
			
				$terms = array( 1 );
				
			}
			
		}
		
		
		// compare
        if( $rule['operator'] == "==") {
        	
        	$match = in_array($term->term_id, $terms);
        
        } elseif( $rule['operator'] == "!=") {
        	
        	$match = !in_array($term->term_id, $terms);
        
        }		
            
        
        // return
        return $match;
        
    }
	
	
	/*
	*  rule_match_post_format
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match_post_format( $match, $rule, $options ) {
		
		// vars
		$post_format = $options['post_format'];
		
		
		// new post format?
		if( !$post_format ) {	
			
			// validate
			if( !$options['post_id'] ) {
			
				return false;
				
			}
			
			
			// post type
			if( !$options['post_type'] ) {
			
				$options['post_type'] = get_post_type( $options['post_id'] );
				
			}
			
		
			// does post_type support 'post-format'
			if( post_type_supports( $options['post_type'], 'post-formats' ) ) {
				
				$post_format = get_post_format( $options['post_id'] );
				
				if( $post_format === false ) {
				
					$post_format = 'standard';
					
				}
				
			}
			
		}

       	
       	// compare
        if( $rule['operator'] == "==") {
        	
        	$match = ( $post_format === $rule['value'] );
        
        } elseif( $rule['operator'] == "!=") {
        	
        	$match = ( $post_format !== $rule['value'] );
        
        }
        
        
        // return        
        return $match;
        
    }
    
    
    /*
	*  rule_match_post_status
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match_post_status( $match, $rule, $options ) {
		
		// validate
		if( !$options['post_id'] ) {
		
			return false;
			
		}
		
					
		// vars
		$post_status = get_post_status( $options['post_id'] );
	    
	    
	    // auto-draft = draft
	    if( $post_status == 'auto-draft' )  {
	    
		    $post_status = 'draft';
		    
	    }
	    
	    
	    // compare
        if( $rule['operator'] == "==") {
        	
        	$match = ( $post_status === $rule['value'] );
        
        } elseif( $rule['operator'] == "!=") {
        	
        	$match = ( $post_status !== $rule['value'] );
        
        }
        
        
        // return
	    return $match;
        
    }


	/*
	*  rule_match_page_type
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
		
	function rule_match_page_type( $match, $rule, $options ) {
	
		// validation
		if( !$options['post_id'] ) {
		
			return false;
			
		}
		
		
		// get post
		$post = get_post( $options['post_id'] );
		
		
		// compare   
        if( $rule['value'] == 'front_page') {
        	
        	// vars
	        $front_page = (int) get_option('page_on_front');
	        
	        
	         // compare
	        if( $rule['operator'] == "==" ) {
	        	
	        	$match = ( $front_page == $post->ID );
	        	
	        } elseif( $rule['operator'] == "!=" ) {
	        	
	        	$match = ( $front_page != $post->ID );
	        
	        }
	        
        } elseif( $rule['value'] == 'posts_page') {
        	
        	// vars
	        $posts_page = (int) get_option('page_for_posts');
	        
	        
	        // compare
	        if( $rule['operator'] == "==" ) {
	        
	        	$match = ( $posts_page == $post->ID );
	        
	        } elseif( $rule['operator'] == "!=" ) {
	        	
	        	$match = ( $posts_page != $post->ID );
	        	
	        }
	        
        } elseif( $rule['value'] == 'top_level') {
        	
        	// vars
        	$post_parent = $post->post_parent;
        	
        	
        	// override
        	if( $options['page_parent'] ) {
	        	
	        	$post_parent = $options['page_parent'];
	        	
        	}
        	
        	
        	// compare
	        if( $rule['operator'] == "==" ) {
	        
	        	$match = ( $post_parent == 0 );
	        
	        } elseif( $rule['operator'] == "!=" ) {
	        	
	        	$match = ( $post_parent != 0 );
	        	
	        }
	            
        } elseif( $rule['value'] == 'parent' ) {
        	
        	// get children
        	$children = get_pages(array(
        		'post_type' => $post->post_type,
        		'child_of' =>  $post->ID,
        	));
        	
	        
	        // compare
	        if( $rule['operator'] == "==" ) {
	        
	        	$match = ( count($children) > 0 );
	        
	        } elseif( $rule['operator'] == "!=" ) {
	        	
	        	$match = ( count($children) == 0 );
	        	
	        }
	        
        } elseif( $rule['value'] == 'child') {
        	
        	// vars
        	$post_parent = $post->post_parent;
        	
        	
        	// override
        	if( $options['page_parent'] ) {
        	
	        	$post_parent = $options['page_parent'];
	        	
        	}
	        
	        
	        // compare
	        if( $rule['operator'] == "==" ) {
	        
	        	$match = ( $post_parent != 0 );
	        
	        } elseif( $rule['operator'] == "!=" ) {
	        	
	        	$match = ( $post_parent == 0 );
	        	
	        }
	        
        }
        
        
        // return
        return $match;

	}
	
	
	/*
	*  rule_match_page_parent
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match_page_parent( $match, $rule, $options ) {
		
		// validation
		if( !$options['post_id'] ) {
		
			return false;
			
		}
		
		
		// vars
		$post = get_post( $options['post_id'] );
		
		
		// post parent
		$post_parent = $post->post_parent;
    	if( $options['page_parent'] ) {
    	
        	$post_parent = $options['page_parent'];
        	
    	}
        
        
        // compare
        if( $rule['operator'] == "==" ) {
        
        	$match = ( $post_parent == $rule['value'] );
        
        } elseif( $rule['operator'] == "!=" ) {
        	
        	$match = ( $post_parent != $rule['value'] );
        	
        }
        
        
        // return
        return $match;

	}
	
	
	/*
	*  rule_match_page_template
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
		
	function rule_match_page_template( $match, $rule, $options ) {
		
		// vars
		$page_template = $options['page_template'];
		
		
		// get page template
		if( !$page_template && $options['post_id'] ) {
		
			$page_template = get_post_meta( $options['post_id'], '_wp_page_template', true );
			
		}
		
		
		// get page template again
		if( ! $page_template ) {
			
			$post_type = $options['post_type'];

			if( !$post_type && $options['post_id'] ) {
			
				$post_type = get_post_type( $options['post_id'] );
				
			}
			
			if( $post_type === 'page' ) {
			
				$page_template = "default";
				
			}
		}
		
		
		// compare
        if( $rule['operator'] == "==" ) {
        
        	$match = ( $page_template === $rule['value'] );
        
        } elseif( $rule['operator'] == "!=" ) {
        	
        	$match = ( $page_template !== $rule['value'] );
        	
        }
        
        
        // return
        return $match;

	}
	
	
    /*
	*  rule_match_user_form
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
    
    function rule_match_user_form( $match, $rule, $options ) {
		
		// vars
		$user_form = $options['user_form'];
		
		
		// add is treated the same as edit
		if( $user_form === 'add' ) {
		
			$user_form = 'edit';
			
		}
		
		
		// compare
		if( $user_form ) {
		
			if( $rule['operator'] == "==" ) {
				
	        	$match = ( $user_form == $rule['value'] );
	        	
	        	
	        	// override for "all"
		        if( $rule['value'] === 'all' ) {
					
					$match = true;
				
				}
	        
	        } elseif( $rule['operator'] == "!=" ) {
	        	
	        	$match = ( $user_form != $rule['value'] );
	        	
	        	
	        	// override for "all"
		        if( $rule['value'] === 'all' ) {
		        
					$match = false;
					
				}
				
	        }
	        
		}
		
        
        // return
        return $match;
    }
    
    
    /*
	*  rule_match_user_role
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match_user_role( $match, $rule, $options ) {
		
		// vars
		$user_id = $options['user_id'];
		$user_role = $options['user_role'];
		
		
		// user form AJAX will send through user_form
		if( $user_role ) {
		
			if( $rule['operator'] == "==" ) {
			
	        	if( $user_role === $rule['value'] ) {
	        	
	        		$match = true;
	        		
	        	}
	        	
	        	
	        	// override for "all"
		        if( $rule['value'] === 'all' ) {
		        
					$match = true;
					
				}
	        
	        } elseif( $rule['operator'] == "!=" ) {
	        	
	        	if( $user_role !== $rule['value'] ) {
	        	
	        		$match = true;
	        		
	        	}
	        	
	        	
	        	// override for "all"
		        if( $rule['value'] === 'all' ) {
		        
					$match = false;
					
				}
				
	        }
	        
		} elseif( $user_id ) {
			
			if( $rule['operator'] == "==" ) {
				
	        	if( $user_id === 'new' ) {
	        		
	        		// case: add user
		        	$match = ( $rule['value'] == get_option('default_role') );
		        	
	        	} else {
	        		
	        		// case: edit user
		        	$match = ( user_can($user_id, $rule['value']) );
		        	
	        	}
	        	
	        	
	        	// override for "all"
		        if( $rule['value'] === 'all' ) {
		        	
					$match = true;
					
				}
				
	        } elseif( $rule['operator'] == "!=" ) {
	        	
	        	if( $user_id === 'new' ) {
	        		
	        		// case: add user
		        	$match = ( $rule['value'] != get_option('default_role') );
		        	
	        	} else {
	        		
	        		// case: edit user
		        	$match = ( !user_can($user_id, $rule['value']) );
		        	
	        	}
	        	
	        	
	        	// override for "all"
		        if( $rule['value'] === 'all' ) {
		        	
					$match = false;
					
				}
				
	        }
	        
		}
		
        
        // return
        return $match;
        
    }
    
    
       
    /*
	*  rule_match_taxonomy
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match_taxonomy( $match, $rule, $options ) {
		
		// vars
		$taxonomy = $options['taxonomy'];
		
		
		// validate
		if( !$taxonomy ) {
			
			return false;
			
		}
		
		
		// compare
		if( $rule['operator'] == "==" ) {
			
        	$match = ( $taxonomy == $rule['value'] );
        	
        	// override for "all"
	        if( $rule['value'] == "all" ) {
	        
				$match = true;
				
			}
			
        } elseif( $rule['operator'] == "!=" ) {
        	
        	$match = ( $taxonomy != $rule['value'] );
        		
        	// override for "all"
	        if( $rule['value'] == "all" ) {
	        	
				$match = false;
				
			}
			
        }
		
        
        // return
        return $match;
        
    }
    
    
    /*
	*  rule_match_attachment
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match_attachment( $match, $rule, $options ) {
		
		// vars
		$attachment = $options['attachment'];
		
		
		// validate
		if( ! $attachment ) {
			
			return false;
			
		}
		
		
		// compare
		if( $rule['operator'] == "==" ) {
			
        	$match = ( $attachment == $rule['value'] );
        	
        	// override for "all"
	        if( $rule['value'] == "all" ) {
	        
				$match = true;
				
			}
			
        } elseif( $rule['operator'] == "!=" ) {
        	
        	$match = ( $attachment != $rule['value'] );
        		
        	// override for "all"
	        if( $rule['value'] == "all" ) {
	        
				$match = false;
				
			}
			
        }
        
        
        // return
        return $match;
        
    }
    
    
    
    /*
	*  rule_match_comment
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
	
	function rule_match_comment( $match, $rule, $options ) {
		
		// vars
		$comment = $options['comment'];
		
		
		// validate
		if( ! $comment ) {
			
			return false;
			
		}
		
		
		// compare
		if( $rule['operator'] == "==" ) {
			
        	$match = ( $comment == $rule['value'] );
        	
        	// override for "all"
	        if( $rule['value'] == "all" ) {
	        
				$match = true;
				
			}
			
        } elseif( $rule['operator'] == "!=" ) {
        	
        	$match = ( $comment != $rule['value'] );
        		
        	// override for "all"
	        if( $rule['value'] == "all" ) {
	        
				$match = false;
				
			}
			
        }
        
        
        // return
        return $match;
        
    }
    
    
    /*
	*  rule_match_widget
	*
	*  This function will match a location rule and return true or false
	*
	*  @type	filter
	*  @date	3/01/13
	*  @since	3.5.7
	*
	*  @param	$match (boolean) 
	*  @param	$rule (array)
	*  @return	$options (array)
	*/
    
    function rule_match_widget( $match, $rule, $options ) {
		
		// vars
		$widget = $options['widget'];
		
		
		// validate
		if( ! $widget ) {
			
			return false;
			
		}
		
		
		// compare
		if( $rule['operator'] == "==" ) {
			
        	$match = ( $widget == $rule['value'] );
        	
        	// override for "all"
	        if( $rule['value'] == "all" ) {
	        
				$match = true;
				
			}
			
        } elseif( $rule['operator'] == "!=" ) {
        	
        	$match = ( $widget != $rule['value'] );
        		
        	// override for "all"
	        if( $rule['value'] == "all" ) {
	        
				$match = false;
				
			}
			
        }
        
                
        // return
        return $match;
    }
			
}

new acf_location();


/*
*  acf_get_field_group_visibility
*
*  This function will look at the given field group's location rules and compare them against
*  the args given to see if this field group is to be shown or not.
*
*  @type	function
*  @date	7/10/13
*  @since	5.0.0
*
*  @param	$field group (array)
*  @param	$args (array)
*  @return	(boolean)
*/

function acf_get_field_group_visibility( $field_group, $args = array() )
{
	// vars
	$args = acf_parse_args($args, array(
		'post_id'		=> 0,
		'post_type'		=> 0,
		'page_template'	=> 0,
		'page_parent'	=> 0,
		'page_type'		=> 0,
		'post_category'	=> array(),
		'post_format'	=> 0,
		'post_taxonomy'	=> array(),
		'taxonomy'		=> 0,
		'user_id'		=> 0,
		'user_role'		=> 0,
		'user_form'		=> 0,
		'attachment'	=> 0,
		'comment'		=> 0,
		'widget'		=> 0,
		'lang'			=> 0,
		'ajax'			=> false
	));
	
	
	// WPML
	if( defined('ICL_LANGUAGE_CODE') ) {
		
		$args['lang'] = ICL_LANGUAGE_CODE;
		
		//global $sitepress;
		//$sitepress->switch_lang( $options['lang'] );
	}
	
	
	// vars
	$visibility = false;
	
	
	// loop through location rules
	foreach( $field_group['location'] as $group_id => $group ) {
		
		// start of as true, this way, any rule that doesn't match will cause this varaible to false
		$match_group = true;
		
		
		// loop over group rules
		if( !empty($group) ) {
		
			foreach( $group as $rule_id => $rule ) {
				
				$match = apply_filters( 'acf/location/rule_match/' . $rule['param'] , false, $rule, $args );
				
				if( !$match )
				{
					$match_group = false;
					break;
				}
				
			}
		}
		
		
		// all rules must havematched!
		if( $match_group ) {
			
			$visibility = true;
			
		}
			
	}

	
	// return
	return $visibility;
}

?>
