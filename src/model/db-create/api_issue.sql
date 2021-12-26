
-- View used by postgrest api

create or replace view api_issue as
    select
        slug,
        name,
        issues
    from person
    where jsonb_array_length(issues) != 0
    order by slug;
