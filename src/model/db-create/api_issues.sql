
-- View used by postgrest api

create or replace view api_issues as
select
    slug,
    ids_in_sources,
    issues
from person
    where issues is not null;
    where cast( jsonb_array_length(issues) AS INTEGER ) != 0;
    where jsonb_array_length(issues) != 0;
    where jsonb_array_length(jsonb_array_elements(issues)) != 0;
    where issues != '{}'::json;
    where issues != '{}'::json;
    where array_length(issues::jsonb, 1) = 0;
    where issues != '{}'::json;
where jsonb_array_length(issues) != 0
;

SELECT json_typeof (issues) from person;

select thing = '{}'::json[];

array_length(  '{}'::json[], 1 ) = 0

SELECT jsonb_array_length(issues) from person AS length;