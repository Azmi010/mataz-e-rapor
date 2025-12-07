<?php

namespace App\Providers\Filament;

use App\Filament\Teacher\Pages\DailyActivityChecklist;
use App\Filament\Teacher\Pages\GradeManagement;
use App\Filament\Teacher\Resources\DailyActivities\DailyActivityResource;
use App\Http\Middleware\RoleRedirectMiddleware;
use App\Models\Teacher;
use App\Models\ClassModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class TeacherPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('teacher')
            ->path('teacher')
            ->loginRouteSlug('/auth/login')
            ->colors([
                'primary' => [
                    50 => '#f0fdf4',
                    100 => '#dcfce7',
                    200 => '#bbf7d0',
                    300 => '#86efac',
                    400 => '#4ade80',
                    500 => '#16a34a',
                    600 => '#15803d',
                    700 => '#166534',
                    800 => '#14532d',
                    900 => '#052e16',
                    950 => '#052e16',
                ],
                'secondary' => [
                    50 => '#fefce8',
                    100 => '#fef9c3',
                    200 => '#fef08a',
                    300 => '#fde047',
                    400 => '#facc15',
                    500 => '#eab308',
                    600 => '#ca8a04',
                    700 => '#a16207',
                    800 => '#854d0e',
                    900 => '#713f12',
                    950 => '#422006',
                ],
            ])
            ->favicon(asset('img/logo.png'))
            ->brandName('MATAZ')
            ->brandLogo(fn () => view('filament.brand'))
            ->discoverResources(in: app_path('Filament/Teacher/Resources'), for: 'App\Filament\Teacher\Resources')
            ->pages([
                Dashboard::class,
                GradeManagement::class,
            ])
            ->navigation(function (\Filament\Navigation\NavigationBuilder $builder): \Filament\Navigation\NavigationBuilder {
                $builder->items([
                    NavigationItem::make('Dashboard')
                        ->icon('heroicon-o-home')
                        ->activeIcon('heroicon-s-home')
                        ->isActiveWhen(fn (): bool => request()->routeIs('filament.teacher.pages.dashboard'))
                        ->url(fn (): string => Dashboard::getUrl()),
                ]);

                if (Auth::check()) {
                    $teacher = Teacher::where('user_id', Auth::id())->first();

                    if ($teacher) {
                        $subjectsWithClasses = DB::table('class_subjects')
                            ->join('subjects', 'class_subjects.subject_id', '=', 'subjects.id')
                            ->join('class_models', 'class_subjects.class_model_id', '=', 'class_models.id')
                            ->where('class_subjects.teacher_id', $teacher->id)
                            ->where('subjects.is_tahfidz', false)
                            ->select(
                                'subjects.id as subject_id',
                                'subjects.name as subject_name',
                                'class_models.id as class_id',
                                'class_models.name as class_name'
                            )
                            ->orderBy('subjects.name')
                            ->orderBy('class_models.name')
                            ->get()
                            ->groupBy('subject_id');

                        $groups = [];
                        foreach ($subjectsWithClasses as $subjectId => $classes) {
                            $subjectName = $classes->first()->subject_name;

                            $groupItems = [];
                            foreach ($classes as $class) {
                                $groupItems[] = NavigationItem::make($class->class_name)
                                    ->url(route('filament.teacher.resources.subject-classes.index', [
                                        'subject' => $subjectId,
                                        'class' => $class->class_id,
                                    ]))
                                    ->isActiveWhen(function () use ($subjectId, $class) {
                                        return request()->route('subject') == $subjectId
                                            && request()->route('class') == $class->class_id;
                                    });
                            }

                            $groups[] = NavigationGroup::make($subjectName)
                                ->items($groupItems)
                                ->icon('heroicon-o-book-open')
                                ->collapsible();
                        }

                        $homeroomClass = ClassModel::where('homeroom_teacher_id', $teacher->id)->first();

                        if ($homeroomClass) {
                            $homeroomItems = [
                                NavigationItem::make('Data Siswa')
                                    ->url(route('filament.teacher.resources.homeroom.students'))
                                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.teacher.resources.homeroom.students')),
                                NavigationItem::make('Absen Siswa')
                                    ->url(route('filament.teacher.resources.homeroom.attendance'))
                                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.teacher.resources.homeroom.attendance')),
                                NavigationItem::make('Catatan Wali Kelas')
                                    ->url(route('filament.teacher.resources.homeroom.notes'))
                                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.teacher.resources.homeroom.notes')),
                                NavigationItem::make('Prestasi')
                                    ->url(route('filament.teacher.resources.homeroom.achievements'))
                                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.teacher.resources.homeroom.achievements')),
                                NavigationItem::make('Rapor Siswa')
                                    ->url(route('filament.teacher.resources.homeroom.reports'))
                                    ->isActiveWhen(fn (): bool => request()->routeIs('filament.teacher.resources.homeroom.reports')),
                            ];

                            $groups[] = NavigationGroup::make('Wali Kelas')
                                ->items($homeroomItems)
                                ->icon('heroicon-o-user-group')
                                ->collapsible();
                        }

                        $builder->groups($groups);
                    }
                }

                return $builder;
            })
            ->discoverWidgets(in: app_path('Filament/Teacher/Widgets'), for: 'App\Filament\Teacher\Widgets')
            ->widgets([
                \App\Filament\Teacher\Widgets\TeacherStatsOverview::class,
                \App\Filament\Teacher\Widgets\UpcomingActivities::class,
                \App\Filament\Teacher\Widgets\RecentStudents::class,
            ])
            ->darkMode(true)
            ->darkModeBrandLogo(fn () => view('filament.brand'))
            ->renderHook('body.end', fn () => view('filament.theme-sync'))
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                RoleRedirectMiddleware::class,
            ]);
    }

}
