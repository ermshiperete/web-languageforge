import * as angular from 'angular';

import {BreadcrumbModule} from '../../core/breadcrumbs/breadcrumb.module';
import {CoreModule} from '../../core/core.module';
import {NoticeModule} from '../../core/notice/notice.module';
import {UserProfileAppComponent} from './user-profile-app.component';

export const UserProfileAppModule = angular
  .module('userprofile', ['ui.bootstrap', 'pascalprecht.translate', CoreModule,
    'palaso.ui.intlTelInput', NoticeModule, BreadcrumbModule
  ])
  .component('userProfileApp', UserProfileAppComponent)
  .config(['$translateProvider', ($translateProvider: angular.translate.ITranslateProvider) => {
    // configure interface language filepath
    $translateProvider.useStaticFilesLoader({
      prefix: '/angular-app/bellows/lang/',
      suffix: '.json'
    });
    $translateProvider.preferredLanguage('en');
    $translateProvider.useSanitizeValueStrategy('escape');
  }])
  .name;
