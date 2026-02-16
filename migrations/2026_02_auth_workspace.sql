START TRANSACTION;

ALTER TABLE `tbl_users`
  ADD COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `password_hash`,
  ADD COLUMN `failed_login_attempts` INT NOT NULL DEFAULT 0 AFTER `is_active`,
  ADD COLUMN `last_login_at` DATETIME NULL AFTER `failed_login_attempts`,
  ADD COLUMN `password_updated_at` DATETIME NULL AFTER `last_login_at`;

CREATE TABLE IF NOT EXISTS `tbl_user_workspaces` (
  `workspace_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `workspace_path` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`workspace_id`),
  UNIQUE KEY `uniq_workspace_user` (`user_id`),
  CONSTRAINT `fk_workspace_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `tbl_files`
  ADD UNIQUE KEY `uniq_user_project_type` (`user_id`, `file_name`, `file_type`);

COMMIT;
