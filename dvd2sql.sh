#!/bin/bash
# Copyright 2019 by Heiko Schäfer <heiko@rangun.de>
#
# This file is part of webvirus.
#
# webvirus is free software: you can redistribute it and/or modify
# it under the terms of the GNU Lesser General Public License as
# published by the Free Software Foundation, either version 3 of
# the License, or (at your option) any later version.
#
# webvirus is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public License
# along with webvirus.  If not, see <http://www.gnu.org/licenses/>.
#

sql_insert() {
  MINFO=`mediainfo --Inform="General;%Title%|%Duration%" "$1"`
  TITLE=`echo "$MINFO" | cut -f1 -d\| | sed 's/ - / – /g'`;
  DURAT=`echo "$MINFO" | cut -f2 -d\|`;
  BNAME=`basename "$1"`;
  printf "'%q'|%q|'%q'" "$TITLE" "$DURAT" "$BNAME" | gawk -F\| '{ printf "INSERT INTO movies (title,duration,filename,disc) VALUES(%s,%d,%s,@lid);\n", $1, $2/1000, $3; }';
}

export -f sql_insert

printf -v DISC "%q" "$1"

echo "INSERT INTO disc (name,vdvd,regular) VALUES('$DISC',0,1);"
echo "SELECT @lid := LAST_INSERT_ID();"
find -L "$2" -type f -execdir /bin/bash -c 'sql_insert "$1"' bash {} \;
