//
//  Copyright 2012 Alin Dobra and Christopher Jermaine
//
//  Licensed under the Apache License, Version 2.0 (the "License");
//  you may not use this file except in compliance with the License.
//  You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
//  Unless required by applicable law or agreed to in writing, software
//  distributed under the License is distributed on an "AS IS" BASIS,
//  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//  See the License for the specific language governing permissions and
//  limitations under the License.
//

#ifndef _INEFFICIENT_MAP_H
#define _INEFFICIENT_MAP_H

/** Changelog

  June 5, 2009; Alin; Added iterator interface on top of the map. The iterator
  interface of the underlying TwoWayList

  June 10, 2009; Alin; Made the copy constructor and opertor= private. Subtle
  bugs appear if the default constructors generated by the compiler are used.
  It is also against the Swapping paradigm.

  June 18, 2009; Alin; Simplified the Swapify and Keyify
  templates. Now they work only if the argument has the == and =
  operator defined. This is not a limitation since all basic types have that
*/

#include <iostream>

#include "Swap.h"
#include "Swapify.h"
#include "TwoWayList.h"
#include "SerializeJson.h"

template <class Key, class Data>
class InefficientMap {

public:
    typedef Key keyType;
    typedef Data dataType;

    // remove all the content
    void Clear(void);

    // inserts the key/data pair into the structure
    void Insert (Key &key, Data &data);

    // this takes the contents of suckMeUp and adds them, all at once,
    // to *this; no checks of any kind are done to remove duplicates, etc.
    void SuckUp (InefficientMap<Key, Data> &suckMeUp);

    // removes one (any) instance of the given key from the map...
    // returns a 1 on success and a zero if the given key was not found
    int Remove (Key &findMe, Key &putKeyHere, Data &putDataHere);

    // attempts to locate the given key
    // returns 1 if it is, 0 otherwise
    int IsThere (const Key &findMe);

    // returns a reference to the data associated with the given search key
    // if the key is not there, then a garbage (newly initialized) Data item is
    // returned.  "Plays nicely" with IsThere in the sense that if IsThere found
    // an item, Find will immediately return that item w/o having to locate it
    Data &Find (const Key &findMe);

    // swap two of the maps
    void swap (InefficientMap<Key, Data> &withMe);

    ///////////// ITERATOR INTERFAACE //////////////
    // look at the current item
    Key& CurrentKey ();
    Data& CurrentData ();

    // move the current pointer position backward through the list
    void Retreat ();

    // move the current pointer position forward through the list
    void Advance ();

    // operations to consult state
    bool AtStart () const;
    bool AtEnd () const;

    // operations to move the the start of end of a list
    void MoveToStart ();
    void MoveToFinish ();

    // Go To/From JSON
    void toJson( Json::Value & dest ) const;
    void fromJson( const Json::Value & src );

private:

    struct Node {
        Key key;
        Data data;

        void swap (Node &swapMe) {
            key.swap (swapMe.key);
            data.swap (swapMe.data);
        }

        void toJson( Json::Value & dest ) const {
            dest = Json::Value(Json::arrayValue);
            ToJson( key, dest[0u] );
            ToJson( data, dest[1u] );
        }

        void fromJson( const Json::Value & src ) {
            FromJson( src[0u], key );
            FromJson( src[1u], data );
        }
    };

    TwoWayList <Node> container;
};

template< typename K, typename V >
void ToJson( const InefficientMap<K, V> & src, Json::Value & dest ) {
    src.toJson(dest);
}

template< typename K, typename V >
void FromJson( const Json::Value & src, InefficientMap<K, V> & dest ) {
    dest.fromJson(src);
}

template< class K, class V >
void swap( InefficientMap<K, V>& a, InefficientMap<K, V>& b ) {
    a.swap(b);
}

#endif
