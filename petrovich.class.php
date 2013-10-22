<?php

class Petrovich {

	public $lastname, $firstname, $middlename, $gender, $mods;
	public $genderlist = array ( 'male', 'female', 'androgynous' );
	public $case = array ( 'nominative', 'genitive', 'dative', 'accusative', 'instrumental', 'prepositional' );
	
	function __construct ( $mod, $lastname, $firstname, $middlename, $gender = null ) {
		if ( strlen( $lastname ) > 0 ) $this->lastname = $lastname;
		if ( strlen( $firstname ) > 0 ) $this->firstname = $firstname;
		if ( strlen( $middlename ) > 0 ) $this->middlename = $middlename;
		if ( strlen( $gender ) > 0 ) $this->gender = $gender;
		else $this->gender = $this->getGender( );
		
		$this->mods = $mod;
	}
	
    # Определение пола по отчеству
    #
    #   getGender('Алексеевич') # => male
    #
    # Если пол не был определён, метод возвращает значение +androgynous+
    #
    #   getGender('блаблабла') # => androgynous
    #
    function getGender( ) {
		switch ( strtolower ( mb_substr ( $this->middlename, -2, 2, 'utf-8' ) ) ) {
			case 'ич':
				return $this->genderlist[0];
				break;
			case 'на':
				return $this->genderlist[1];
				break;
			default:
				return $this->genderlist[2];
		}
    }
	
	function tagsAllow ( $tags, $ruleTags ) {
		if ( is_array( $ruleTags ) && is_array( $tags ) )
			for ( $i = 0; $i < count( $ruleTags ); $i++ ) {
				echo $ruleTags [ $i ] . '<br />';
				print_r( $tags );
				if ( ! $tags [ $ruleTags [ $i ] ] ) {
					return false;
				}
			}
		return true;
	}

	function find ( $name, $gender, $rules, $wholeWord, $tags ) {
		$name = strtolower( $name );
		for ( $i = 0; $i < count( $rules ); $i++ ) {
			$rule = $rules[ $i ];
			if ( $this->tagsAllow ( $tags, ( isset( $rule [ 'tags' ] ) ? $rule [ 'tags' ] : '' ) ) && ( $rule [ 'gender' ] == $this->genderlist[ 2 ] || $rule [ 'gender' ] == $gender ) ) {
				for ( $j = 0; $j < count( $rule[ 'test' ] ); $j++) {
					$test = $rule[ 'test' ][ $j ];
					if ( $test == ( $wholeWord ? $name : substr( $name, -strlen( $test ) ) ) ) {
						return $rule;
					}
				}
			}
		}
		return false;
	}
	
	function findFor ( $name, $gender, $rules, $tags ) {
		if ( $rules[ 'exceptions' ] ) {
			if ( $this->find( $name, $gender, $rules[ 'exceptions' ], true, $tags ) )
				return $this->find( $name, $gender, $rules[ 'exceptions' ], true, $tags );
			else
				return  $this->find( $name, $gender, $rules[ 'suffixes' ], false, $tags );
		} else
				return  $this->find( $name, $gender, $rules[ 'suffixes' ], false, $tags );
		//return ( $rules[ 'exceptions' ] && $this->find( $name, $gender, $rules[ 'exceptions' ], true, $tags ) ) || $this->find( $name, $gender, $rules[ 'suffixes' ], false, $tags );
	}
	
	function modificatorFor ( $gcase, $rule ) {
		if ( $gcase == $this->case[0] ) {
			return '.';
		} elseif ( isset ( $this->mods ) ) {
			return $rule[ 'mods' ][ array_search( $gcase, $this->case ) ];
		} else {
			die( 'Unknown grammatic case: ' . $gcase );
		}
	}
	
	function apply ( $name, $gcase, $rule ) {
		$mod = $this->modificatorFor( $gcase, $rule );
		for ( $i = 0; $i < strlen( $mod ); $i++ ) {
			$ch = $mod{ $i };
			if ( $ch == '-' ) {
				$name = mb_substr( $name, 0, mb_strlen( $name, 'utf-8' ) - 1, 'utf-8' ); // TODO: improve performance here
			} elseif ( $ch !== '.') {
				$name .= $ch;
			}
		}
		return $name;
	}
	
	function findAndApply ( $name, $gcase, $gender, $rules, $tags) {
		$rule = $this->findFor( $name, $gender, $rules, $tags );
		return $rule ? $this->apply( $name, $gcase, $rule ) : $name;
	}
	
	function inflect ( $name, $gcase, $gender, $rules ) {
		$parts = explode( '-', $name );
		for ( $i = 0; $i < count( $parts ); $i++) {
			$parts[ $i ] = $this->findAndApply( $parts[ $i ], $gcase, $gender, $rules, ( ( $i == 0 ) && ( count( $parts ) > 1 ) ) ? 'first_word' : '' );
		}
		return implode( '-', $parts );
	}
}
