
-- View used by postgrest api
create or replace view view_wikiproject_issue as
select
    i.id            "issue_id",
    i.slug          "issue_slug",
    i.description   "issue_description",
    wp.id           "project_id",
    wp.slug         "project_slug"
from
    issue               i,
    wikiproject         wp,
    wikiproject_issue
where i.id = wikiproject_issue.id_issue
  and wp.id = wikiproject_issue.id_project
;
