
-- Persons' issues
/* 
    NOT YET USED
*/
create table issue (
    slug            varchar(255) primary key,
    author          varchar(255) not null default '',
    description     text,
);
